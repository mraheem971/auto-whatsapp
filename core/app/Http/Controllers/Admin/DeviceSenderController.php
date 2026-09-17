<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\DeviceMessage;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeviceSenderController extends Controller
{
    /**
     * Web Admin Device Messenger Interface
     */
    public function index()
    {
        $pageTitle = 'Device Messenger - Send from Android Device';
        $connectedAccounts = WhatsappAccount::adminOnly()->active()->latest()->get();
        $primaryAccount = WhatsappAccount::adminOnly()->active()->latest()->first();
        $templates = MessageTemplate::latest()->get();
        $contactLists = ContactList::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })->withCount('contacts')->latest()->get();
        
        // Fetch groups
        $groups = Contact::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })
            ->where('type', 'group')
            ->whereNotNull('group_id')
            ->where('group_id', '!=', '')
            ->selectRaw('group_name, group_id')
            ->groupBy('group_name', 'group_id')
            ->get();

        // Recent 5 messages
        $recentMessages = DeviceMessage::latest()->take(5)->get();

        return view('admin.device.sender', compact(
            'pageTitle',
            'connectedAccounts',
            'primaryAccount',
            'templates',
            'contactLists',
            'groups',
            'recentMessages'
        ));
    }

    /**
     * Send Message (Single / Batch) from Selected Android Device via Web Admin
     */
    public function send(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|string',
            'receiver'   => 'required|string',
            'message'    => 'nullable|string',
            'media_url'  => 'nullable|url',
            'media_type' => 'nullable|in:text,image,video,audio,document',
            'filename'   => 'nullable|string',
            'file'       => 'nullable|file|max:30720', // Max 30MB
        ]);

        $messageText = $request->message ?: '';
        $mediaUrl = $request->media_url;
        $mediaType = $request->media_type ?: 'text';
        $filename = $request->filename;

        // Handle uploaded file
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if ($uploadedFile->isValid()) {
                $filename = $filename ?: $uploadedFile->getClientOriginalName();
                $path = $uploadedFile->store('device_uploads', 'public');
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

        if (empty($messageText) && empty($mediaUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either a message text or attach a media file.'
            ], 422);
        }

        // Determine account
        $account = null;
        if ($request->session_id) {
            $account = WhatsappAccount::adminOnly()->where('session_id', $request->session_id)->first();
            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied: Admin cannot use user WhatsApp accounts.'
                ], 403);
            }
        }
        if (!$account) {
            $account = WhatsappAccount::adminOnly()->active()->latest()->first();
        }

        if (!$account || empty($account->session_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No active connected admin WhatsApp device found. Please scan the QR code in WhatsApp Accounts first.'
            ], 400);
        }

        // Parse recipients (comma separated, new lines, or space)
        $rawReceivers = preg_split('/[\r\n,]+/', $request->receiver);
        $receivers = array_filter(array_map('trim', $rawReceivers));

        if (empty($receivers)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter at least one valid recipient phone number or group ID.'
            ], 422);
        }

        $results = [];
        $sentCount = 0;
        $failedCount = 0;

        foreach ($receivers as $target) {
            if (empty($target)) continue;

            $isGroup = str_ends_with($target, '@g.us') || (str_starts_with($target, '120') && strlen($target) >= 18);
            $cleanTarget = preg_replace('/[^0-9]/', '', $target);
            $targetJid = $isGroup ? (str_ends_with($target, '@g.us') ? $target : "{$target}@g.us") : "{$cleanTarget}@s.whatsapp.net";

            // Find contact name if available
            $contactName = $isGroup ? 'Group' : 'Customer';
            $contact = Contact::where('phone_number', $cleanTarget)->orWhere('group_id', $target)->first();
            if ($contact && $contact->name) {
                $contactName = $contact->name;
            }

            // Replace dynamic shortcodes
            $finalMessage = str_replace(
                ['{name}', '{phone}', '{date}', '{time}'],
                [$contactName, $cleanTarget, date('Y-m-d'), date('h:i A')],
                $messageText
            );

            // Create initial database log record
            $deviceMsg = new DeviceMessage();
            $deviceMsg->admin_id            = auth('admin')->id() ?? 1;
            $deviceMsg->whatsapp_account_id = $account->id;
            $deviceMsg->session_id          = $account->session_id;
            $deviceMsg->device_name         = $account->account_name;
            $deviceMsg->sender_phone        = $account->phone_number;
            $deviceMsg->receiver            = $target;
            $deviceMsg->receiver_name       = $contactName;
            $deviceMsg->target_jid          = $targetJid;
            $deviceMsg->is_group            = $isGroup ? 1 : 0;
            $deviceMsg->group_name          = $isGroup ? ($contact ? $contact->group_name : $target) : null;
            $deviceMsg->message             = $finalMessage;
            $deviceMsg->media_url           = $mediaUrl;
            $deviceMsg->media_type          = $mediaType;
            $deviceMsg->filename            = $filename;
            $deviceMsg->status              = 'pending';
            $deviceMsg->send_mode           = 'web_admin';
            $deviceMsg->save();

            try {
                $payload = [
                    'sessionId' => $account->session_id,
                    'receiver'  => $targetJid,
                    'message'   => $finalMessage,
                    'isGroup'   => $isGroup ? 1 : 0,
                    'mediaUrl'  => $mediaUrl,
                    'mediaType' => $mediaType,
                    'filename'  => $filename,
                ];

                $response = BaileysClient::post('api/messages/send', $payload, 25);
                $resData = $response->json();

                if ($response->successful() && ($resData['success'] ?? false)) {
                    $sentCount++;
                    $deviceMsg->status     = 'sent';
                    $deviceMsg->message_id = $resData['messageId'] ?? null;
                    $deviceMsg->save();

                    $results[] = [
                        'id'         => $deviceMsg->id,
                        'receiver'   => $target,
                        'target_jid' => $targetJid,
                        'status'     => 'sent',
                        'message_id' => $deviceMsg->message_id,
                        'message'    => 'Sent successfully'
                    ];
                } else {
                    $failedCount++;
                    $error = $resData['error'] ?? 'Delivery failed';
                    $deviceMsg->status        = 'failed';
                    $deviceMsg->error_message = $error;
                    $deviceMsg->save();

                    $results[] = [
                        'id'       => $deviceMsg->id,
                        'receiver' => $target,
                        'status'   => 'failed',
                        'error'    => $error
                    ];
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $deviceMsg->status        = 'failed';
                $deviceMsg->error_message = $e->getMessage();
                $deviceMsg->save();

                $results[] = [
                    'id'       => $deviceMsg->id,
                    'receiver' => $target,
                    'status'   => 'failed',
                    'error'    => $e->getMessage()
                ];
            }
        }

        $allSuccess = ($failedCount === 0);
        return response()->json([
            'success'      => $allSuccess,
            'total'        => count($results),
            'sent_count'   => $sentCount,
            'failed_count' => $failedCount,
            'results'      => $results,
            'message'      => $allSuccess 
                ? 'Message dispatched successfully to ' . $sentCount . ' recipient(s)!' 
                : ($sentCount > 0 ? "Dispatched {$sentCount} message(s), {$failedCount} failed." : "Failed to dispatch message(s).")
        ]);
    }

    /**
     * Sent Messages History Logs
     */
    public function logs(Request $request)
    {
        $pageTitle = 'Device Message Logs';
        $query = DeviceMessage::latest();

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('receiver', 'LIKE', "%{$search}%")
                  ->orWhere('receiver_name', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%")
                  ->orWhere('sender_phone', 'LIKE', "%{$search}%")
                  ->orWhere('device_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->media_type) {
            $query->where('media_type', $request->media_type);
        }

        if ($request->date) {
            $dates = explode('-', $request->date);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', trim($dates[0]))
                      ->whereDate('created_at', '<=', trim($dates[1]));
            }
        }

        $messages = $query->paginate(getPaginate());
        $connectedAccounts = WhatsappAccount::latest()->get();

        return view('admin.device.logs', compact('pageTitle', 'messages', 'connectedAccounts'));
    }

    /**
     * Resend a single message
     */
    public function resend($id)
    {
        $deviceMsg = DeviceMessage::findOrFail($id);
        $account = WhatsappAccount::where('session_id', $deviceMsg->session_id)->first() 
            ?: WhatsappAccount::active()->latest()->first();

        if (!$account || empty($account->session_id)) {
            $notify[] = ['error', 'No active WhatsApp account connected for resending.'];
            return back()->withNotify($notify);
        }

        try {
            $payload = [
                'sessionId' => $account->session_id,
                'receiver'  => $deviceMsg->target_jid ?: $deviceMsg->receiver,
                'message'   => $deviceMsg->message,
                'isGroup'   => $deviceMsg->is_group ? 1 : 0,
                'mediaUrl'  => $deviceMsg->media_url,
                'mediaType' => $deviceMsg->media_type,
                'filename'  => $deviceMsg->filename,
            ];

            $response = BaileysClient::post('api/messages/send', $payload, 25);
            $resData = $response->json();

            if ($response->successful() && ($resData['success'] ?? false)) {
                $deviceMsg->status        = 'sent';
                $deviceMsg->message_id    = $resData['messageId'] ?? $deviceMsg->message_id;
                $deviceMsg->error_message = null;
                $deviceMsg->save();

                $notify[] = ['success', 'Message resent successfully!'];
            } else {
                $deviceMsg->status        = 'failed';
                $deviceMsg->error_message = $resData['error'] ?? 'Resend failed';
                $deviceMsg->save();

                $notify[] = ['error', 'Resend failed: ' . ($resData['error'] ?? 'Unknown error')];
            }
        } catch (\Throwable $e) {
            $deviceMsg->status        = 'failed';
            $deviceMsg->error_message = $e->getMessage();
            $deviceMsg->save();

            $notify[] = ['error', 'Resend error: ' . $e->getMessage()];
        }

        return back()->withNotify($notify);
    }

    /**
     * Delete a single message log
     */
    public function deleteLog($id)
    {
        $deviceMsg = DeviceMessage::findOrFail($id);
        $deviceMsg->delete();

        $notify[] = ['success', 'Message log deleted successfully.'];
        return back()->withNotify($notify);
    }

    /**
     * Clear all message logs
     */
    public function clearLogs()
    {
        DeviceMessage::truncate();
        $notify[] = ['success', 'All message logs have been cleared.'];
        return back()->withNotify($notify);
    }

    /**
     * Android Gateway & Mobile App Integration Documentation
     */
    public function gateway()
    {
        $pageTitle = 'Android Mobile Gateway Hub';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        $primaryAccount = WhatsappAccount::active()->latest()->first();
        $baseUrl = url('/');

        return view('admin.device.gateway', compact('pageTitle', 'connectedAccounts', 'primaryAccount', 'baseUrl'));
    }
}
