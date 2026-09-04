<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageApiController extends Controller
{
    /**
     * List all active/connected WhatsApp accounts available for API sending
     * GET /api/accounts
     */
    public function accounts()
    {
        $accounts = WhatsappAccount::latest()->get()->map(function ($acc) {
            return [
                'id'           => $acc->id,
                'name'         => $acc->account_name,
                'phone_number' => $acc->phone_number,
                'session_id'   => $acc->session_id,
                'status'       => $acc->status == 1 ? 'connected' : 'disconnected',
                'connected'    => $acc->status == 1,
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $accounts->count(),
            'accounts'=> $accounts
        ]);
    }

    /**
     * Send WhatsApp Message (Text or Media)
     * POST /api/send-message
     * POST /api/v1/send-message
     */
    public function sendMessage(Request $request)
    {
        // Support flexible parameter names (to, receiver, phone, number)
        $receiver = $request->input('receiver') 
            ?: $request->input('to') 
            ?: $request->input('phone') 
            ?: $request->input('number') 
            ?: $request->input('recipient');

        $message = $request->input('message') 
            ?: $request->input('text') 
            ?: $request->input('body') 
            ?: $request->input('caption', '');

        $mediaUrl = $request->input('media_url') 
            ?: $request->input('image_url') 
            ?: $request->input('file_url') 
            ?: $request->input('document_url') 
            ?: $request->input('video_url') 
            ?: $request->input('audio_url');

        $mediaType = strtolower($request->input('media_type') ?: $request->input('type', 'text'));
        $filename = $request->input('filename', '');
        $sessionId = $request->input('session_id');
        $accountId = $request->input('account_id');
        $accountPhone = $request->input('account_phone');

        // Handle uploaded file if present
        if ($request->hasFile('file') || $request->hasFile('media')) {
            $uploadedFile = $request->file('file') ?: $request->file('media');
            if ($uploadedFile && $uploadedFile->isValid()) {
                $filename = $filename ?: $uploadedFile->getClientOriginalName();
                $path = $uploadedFile->store('api_uploads', 'public');
                $mediaUrl = asset('storage/' . $path);
                
                $mime = $uploadedFile->getMimeType();
                if (str_starts_with($mime, 'image/')) {
                    $mediaType = 'image';
                } elseif (str_starts_with($mime, 'video/')) {
                    $mediaType = 'video';
                } elseif (str_starts_with($mime, 'audio/')) {
                    $mediaType = 'audio';
                } else {
                    $mediaType = 'document';
                }
            }
        }

        if (empty($receiver)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'The receiver (phone number or group JID) is required. e.g. "923001234567" or "120363xxx@g.us"'
            ], 422);
        }

        if (empty($message) && empty($mediaUrl)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Either message text or media_url must be provided.'
            ], 422);
        }

        // Determine which WhatsApp account session to use
        $account = null;
        if ($sessionId) {
            $account = WhatsappAccount::where('session_id', $sessionId)->first();
        } elseif ($accountId) {
            $account = WhatsappAccount::find($accountId);
        } elseif ($accountPhone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $accountPhone);
            $account = WhatsappAccount::where('phone_number', 'LIKE', "%{$cleanPhone}%")->first();
        }

        // If no specific account requested or found, use latest active connected account
        if (!$account) {
            $account = WhatsappAccount::active()->latest()->first();
        }

        if (!$account || empty($account->session_id)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'No active connected WhatsApp account found. Please connect an account in the admin panel first.'
            ], 400);
        }

        // Handle multiple comma-separated recipients or array
        $recipientsList = is_array($receiver) ? $receiver : explode(',', $receiver);
        $results = [];
        $hasErrors = false;

        foreach ($recipientsList as $target) {
            $target = trim($target);
            if (empty($target)) continue;

            $isGroup = str_ends_with($target, '@g.us') || (str_starts_with($target, '120') && strlen($target) >= 18);
            
            // Format phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $target);
            $targetJid = $isGroup ? (str_ends_with($target, '@g.us') ? $target : "{$target}@g.us") : "{$cleanPhone}@s.whatsapp.net";

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
                    $results[] = [
                        'receiver'   => $target,
                        'target_jid' => $resData['targetJid'] ?? $targetJid,
                        'message_id' => $resData['messageId'] ?? null,
                        'status'     => 'sent',
                        'message'    => 'Message sent successfully!',
                        'timestamp'  => now()->toIso8601String()
                    ];
                } else {
                    $hasErrors = true;
                    $results[] = [
                        'receiver'   => $target,
                        'target_jid' => $targetJid,
                        'status'     => 'failed',
                        'error'      => $resData['error'] ?? 'Delivery failed'
                    ];
                }
            } catch (\Throwable $e) {
                $hasErrors = true;
                $results[] = [
                    'receiver'   => $target,
                    'target_jid' => $targetJid,
                    'status'     => 'failed',
                    'error'      => $e->getMessage()
                ];
            }
        }

        // Single recipient response format
        if (count($results) === 1) {
            $single = $results[0];
            $isSuccess = $single['status'] === 'sent';
            return response()->json([
                'success' => $isSuccess,
                'status'  => $single['status'],
                'message' => $isSuccess ? 'Message sent successfully!' : ($single['error'] ?? 'Delivery failed'),
                'data'    => array_merge($single, [
                    'account' => [
                        'name'         => $account->account_name,
                        'phone_number' => $account->phone_number,
                        'session_id'   => $account->session_id,
                    ]
                ])
            ], $isSuccess ? 200 : 400);
        }

        // Batch recipients response
        return response()->json([
            'success'      => !$hasErrors,
            'total'        => count($results),
            'sent_count'   => count(array_filter($results, fn($r) => $r['status'] === 'sent')),
            'failed_count' => count(array_filter($results, fn($r) => $r['status'] === 'failed')),
            'account'      => [
                'name'         => $account->account_name,
                'phone_number' => $account->phone_number,
                'session_id'   => $account->session_id,
            ],
            'results'      => $results
        ], $hasErrors ? 207 : 200);
    }
}
