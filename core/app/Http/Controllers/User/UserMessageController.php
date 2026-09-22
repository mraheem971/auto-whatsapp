<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DeviceMessage;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;

class UserMessageController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Direct WhatsApp Messaging & Logs';
        $user = auth()->user();
        $plan = $user->currentPlan();

        $accounts = WhatsappAccount::where('user_id', $user->id)->active()->latest()->get();
        $templates = MessageTemplate::where('user_id', $user->id)->latest()->get();

        $query = DeviceMessage::where('user_id', $user->id);

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('receiver', 'LIKE', "%{$search}%")
                  ->orWhere('receiver_name', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $messages = $query->latest()->paginate(getPaginate());
        $totalSent = DeviceMessage::where('user_id', $user->id)->where('status', 'sent')->count();
        $totalFailed = DeviceMessage::where('user_id', $user->id)->where('status', 'failed')->count();

        return view('Template::user.messages.index', compact(
            'pageTitle',
            'user',
            'plan',
            'accounts',
            'templates',
            'messages',
            'totalSent',
            'totalFailed'
        ));
    }

    public function send(Request $request)
    {
        $request->validate([
            'session_id'   => 'required|string',
            'receiver'     => 'required|string',
            'message'      => 'required|string',
            'media_url'    => 'nullable|url',
            'media_type'   => 'nullable|in:image,video,document,audio',
        ]);

        $user = auth()->user();
        $account = WhatsappAccount::where('user_id', $user->id)->where('session_id', $request->session_id)->first();

        if (!$account) {
            $notify[] = ['error', 'WhatsApp account session not found or was removed.'];
            return back()->withNotify($notify);
        }

        if ($account->status != 1) {
            $notify[] = ['error', "WhatsApp account '{$account->account_name}' is not connected. Please connect or link the account first."];
            return back()->withNotify($notify);
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->receiver);
        $payload = [
            'sessionId' => $account->session_id,
            'receiver'  => $cleanPhone,
            'message'   => $request->message,
        ];

        if ($request->media_url) {
            $payload['mediaUrl'] = $request->media_url;
            $payload['mediaType'] = $request->media_type ?: 'image';
        }

        $res = BaileysClient::post('api/messages/send', $payload, 15);

        $msgRecord = new DeviceMessage();
        $msgRecord->user_id             = $user->id;
        $msgRecord->whatsapp_account_id = $account->id;
        $msgRecord->session_id          = $account->session_id;
        $msgRecord->device_name         = $account->account_name;
        $msgRecord->sender_phone        = $account->phone_number;
        $msgRecord->receiver            = $cleanPhone;
        $msgRecord->target_jid          = "{$cleanPhone}@s.whatsapp.net";
        $msgRecord->message             = $request->message;
        $msgRecord->media_url           = $request->media_url;
        $msgRecord->media_type          = $request->media_type;
        $msgRecord->send_mode           = 'direct';

        $isSuccess = false;
        $messageId = null;
        $errorMessage = 'Connection error';

        if ($res && $res->successful()) {
            $data = $res->json();
            if ((isset($data['status']) && $data['status'] === 'success') || (isset($data['success']) && $data['success'] === true)) {
                $isSuccess = true;
                $messageId = $data['messageId'] ?? null;
            } else {
                $errorMessage = $data['message'] ?? ($data['error'] ?? 'Gateway failed to deliver message.');
            }
        } elseif ($res) {
            $errorMessage = $res->json('error') ?? ($res->json('message') ?? 'Gateway returned an error.');
            if (str_contains(strtolower($errorMessage), 'not connected') || str_contains(strtolower($errorMessage), 'scan the qr')) {
                $account->update(['status' => 0]);
            }
        }

        if ($isSuccess) {
            $msgRecord->status = 'sent';
            $msgRecord->message_id = $messageId;
            $msgRecord->save();

            $notify[] = ['success', "Message sent successfully to +{$cleanPhone}!"];
            return back()->withNotify($notify);
        }

        $msgRecord->status = 'failed';
        $msgRecord->error_message = $errorMessage;
        $msgRecord->save();

        $notify[] = ['error', 'Failed to send message: ' . $errorMessage];
        return back()->withNotify($notify);
    }
}