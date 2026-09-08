<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceMessage;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    /**
     * Get Device Info & Status
     * GET /api/device/info
     */
    public function info(Request $request)
    {
        $accounts = WhatsappAccount::all()->map(function ($acc) {
            return [
                'id'           => $acc->id,
                'device_name'  => $acc->account_name,
                'phone_number' => $acc->phone_number,
                'session_id'   => $acc->session_id,
                'status'       => $acc->status == 1 ? 'online' : 'offline',
                'is_connected' => $acc->status == 1,
                'last_seen'    => $acc->updated_at ? $acc->updated_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'devices' => $accounts
        ]);
    }

    /**
     * Send Message from Device via API
     * POST /api/device/send
     */
    public function send(Request $request)
    {
        $receiver = $request->input('receiver') ?: $request->input('to') ?: $request->input('phone');
        $message = $request->input('message') ?: $request->input('text') ?: $request->input('caption', '');
        $mediaUrl = $request->input('media_url') ?: $request->input('image_url') ?: $request->input('file_url');
        $mediaType = strtolower($request->input('media_type') ?: 'text');
        $filename = $request->input('filename', '');
        $sessionId = $request->input('session_id');

        if (empty($receiver)) {
            return response()->json([
                'success' => false,
                'message' => 'The receiver phone number or group JID is required.'
            ], 422);
        }

        if (empty($message) && empty($mediaUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Either message text or media_url is required.'
            ], 422);
        }

        $account = $sessionId ? WhatsappAccount::where('session_id', $sessionId)->first() : null;
        if (!$account) {
            $account = WhatsappAccount::active()->latest()->first();
        }

        if (!$account || empty($account->session_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No connected WhatsApp Android device found.'
            ], 400);
        }

        $isGroup = str_ends_with($receiver, '@g.us') || (str_starts_with($receiver, '120') && strlen($receiver) >= 18);
        $cleanTarget = preg_replace('/[^0-9]/', '', $receiver);
        $targetJid = $isGroup ? (str_ends_with($receiver, '@g.us') ? $receiver : "{$receiver}@g.us") : "{$cleanTarget}@s.whatsapp.net";

        $deviceMsg = new DeviceMessage();
        $deviceMsg->admin_id            = 1;
        $deviceMsg->whatsapp_account_id = $account->id;
        $deviceMsg->session_id          = $account->session_id;
        $deviceMsg->device_name         = $account->account_name;
        $deviceMsg->sender_phone        = $account->phone_number;
        $deviceMsg->receiver            = $receiver;
        $deviceMsg->target_jid          = $targetJid;
        $deviceMsg->is_group            = $isGroup ? 1 : 0;
        $deviceMsg->message             = $message;
        $deviceMsg->media_url           = $mediaUrl;
        $deviceMsg->media_type          = $mediaType;
        $deviceMsg->filename            = $filename;
        $deviceMsg->status              = 'pending';
        $deviceMsg->send_mode           = 'api';
        $deviceMsg->save();

        try {
            $payload = [
                'sessionId' => $account->session_id,
                'receiver'  => $targetJid,
                'message'   => $message,
                'isGroup'   => $isGroup ? 1 : 0,
                'mediaUrl'  => $mediaUrl,
                'mediaType' => $mediaType,
                'filename'  => $filename,
            ];

            $response = BaileysClient::post('api/messages/send', $payload, 25);
            $resData = $response->json();

            if ($response->successful() && ($resData['success'] ?? false)) {
                $deviceMsg->status     = 'sent';
                $deviceMsg->message_id = $resData['messageId'] ?? null;
                $deviceMsg->save();

                return response()->json([
                    'success'    => true,
                    'status'     => 'sent',
                    'message'    => 'Message sent from Android device successfully!',
                    'data'       => [
                        'log_id'      => $deviceMsg->id,
                        'message_id'  => $deviceMsg->message_id,
                        'receiver'    => $receiver,
                        'target_jid'  => $targetJid,
                        'device_name' => $account->account_name,
                        'sender'      => $account->phone_number,
                    ]
                ]);
            } else {
                $error = $resData['error'] ?? 'Delivery failed';
                $deviceMsg->status        = 'failed';
                $deviceMsg->error_message = $error;
                $deviceMsg->save();

                return response()->json([
                    'success' => false,
                    'status'  => 'failed',
                    'message' => $error,
                    'log_id'  => $deviceMsg->id
                ], 400);
            }
        } catch (\Throwable $e) {
            $deviceMsg->status        = 'failed';
            $deviceMsg->error_message = $e->getMessage();
            $deviceMsg->save();

            return response()->json([
                'success' => false,
                'status'  => 'failed',
                'message' => $e->getMessage(),
                'log_id'  => $deviceMsg->id
            ], 500);
        }
    }

    /**
     * Get Pending Outgoing Messages for Android Gateway App
     * GET /api/device/messages/pending
     */
    public function pendingMessages(Request $request)
    {
        $limit = min((int) $request->input('limit', 20), 100);
        $query = DeviceMessage::where('status', 'pending')->orderBy('id', 'asc');

        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        $messages = $query->take($limit)->get();

        return response()->json([
            'success' => true,
            'count'   => $messages->count(),
            'data'    => $messages
        ]);
    }

    /**
     * Update Message Status from Android Gateway App
     * POST /api/device/messages/status
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|integer',
            'status' => 'required|in:sent,delivered,failed',
        ]);

        $message = DeviceMessage::find($request->id);
        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message record not found'], 404);
        }

        $message->status = $request->status;
        if ($request->message_id) {
            $message->message_id = $request->message_id;
        }
        if ($request->error_message) {
            $message->error_message = $request->error_message;
        }
        $message->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data'    => $message
        ]);
    }
}
