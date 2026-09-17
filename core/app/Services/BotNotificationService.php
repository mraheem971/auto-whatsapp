<?php

namespace App\Services;

use App\Models\BotNotificationLog;
use App\Models\BotNotificationSetting;
use App\Models\BotScheduledTask;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BotNotificationService
{
    /**
     * Send Alert Notification to Admin WhatsApp
     */
    public static function notifyAdmin($eventType, $title, $details, array $metadata = [])
    {
        $settings = BotNotificationSetting::getSettings();
        $adminPhone = $settings->admin_whatsapp_number;

        if (empty($adminPhone)) {
            Log::warning('[BotNotification] No admin WhatsApp number configured.');
            return false;
        }

        // Check preference toggle
        if ($eventType === 'system_error' && !$settings->notify_on_system_error) {
            return false;
        }
        if ($eventType === 'user_error_escalation' && !$settings->notify_on_user_error_escalation) {
            return false;
        }
        if ($eventType === 'session_disconnect' && !$settings->notify_on_session_disconnect) {
            return false;
        }
        if ($eventType === 'new_message' && !$settings->notify_on_new_message) {
            return false;
        }
        if ($eventType === 'reminder' && !$settings->notify_on_scheduled_reminder) {
            return false;
        }

        // Format clean WhatsApp message
        $formattedMessage = "🔔 *BOT NOTIFICATION ALERT*\n";
        $formattedMessage .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $formattedMessage .= "📌 *Event:* " . strtoupper(str_replace('_', ' ', $eventType)) . "\n";
        $formattedMessage .= "🏷️ *Subject:* " . $title . "\n\n";
        $formattedMessage .= $details . "\n";
        $formattedMessage .= "\n━━━━━━━━━━━━━━━━━━━━━\n";
        $formattedMessage .= "⏰ *Timestamp:* " . date('d M Y, h:i:s A') . "\n";
        $formattedMessage .= "🤖 *Auto-WhatsApp Notification Engine*";

        return self::sendDirectNotification(
            $adminPhone,
            $formattedMessage,
            null,
            'text',
            $eventType,
            $title,
            'admin',
            $metadata
        );
    }

    /**
     * Handle incoming message from customer and trigger admin escalation if error keywords matched
     */
    public static function handleIncomingMessageAlert($sessionId, $senderPhone, $incomingText, $pushName = '')
    {
        $settings = BotNotificationSetting::getSettings();
        $lowerText = mb_strtolower(trim($incomingText));
        $keywords = $settings->keywords_array;
        $isErrorKeyword = false;

        foreach ($keywords as $kw) {
            if (!empty($kw) && str_contains($lowerText, $kw)) {
                $isErrorKeyword = true;
                break;
            }
        }

        // If error keyword matched and user error escalation is enabled
        if ($isErrorKeyword && $settings->notify_on_user_error_escalation) {
            $customerName = $pushName ?: "+{$senderPhone}";
            $details = "👤 *Customer:* {$customerName}\n";
            $details .= "📱 *Customer Phone:* +{$senderPhone}\n";
            $details .= "💬 *Customer Message:* \"{$incomingText}\"\n";
            $details .= "⚠️ *Reason:* Error keyword matched in customer message.";

            self::notifyAdmin(
                'user_error_escalation',
                "Customer Error Reported by {$customerName}",
                $details,
                [
                    'session_id'   => $sessionId,
                    'sender_phone' => $senderPhone,
                    'customer_name'=> $customerName,
                    'raw_message'  => $incomingText
                ]
            );

            // Optional auto-reply to customer
            if ($settings->auto_error_reply_to_user && !empty($settings->error_reply_message)) {
                $reply = str_replace(
                    ['{name}', '{phone}', '{date}', '{time}'],
                    [$customerName, $senderPhone, date('Y-m-d'), date('h:i A')],
                    $settings->error_reply_message
                );

                self::sendDirectNotification(
                    $senderPhone,
                    $reply,
                    null,
                    'text',
                    'user_error_escalation',
                    'Auto Error Assurance Reply',
                    'customer',
                    ['session_id' => $sessionId]
                );
            }
        } elseif ($settings->notify_on_new_message) {
            $customerName = $pushName ?: "+{$senderPhone}";
            $details = "👤 *From:* {$customerName} (+{$senderPhone})\n";
            $details .= "💬 *Message:* \"{$incomingText}\"";

            self::notifyAdmin(
                'new_message',
                "New Incoming Message from {$customerName}",
                $details,
                [
                    'session_id'   => $sessionId,
                    'sender_phone' => $senderPhone,
                    'customer_name'=> $customerName,
                    'raw_message'  => $incomingText
                ]
            );
        }
    }

    /**
     * Dispatch notification to any recipient and create audit log
     */
    public static function sendDirectNotification(
        $recipient,
        $message,
        $mediaUrl = null,
        $mediaType = 'text',
        $eventType = 'custom',
        $title = null,
        $recipientType = 'customer',
        array $metadata = []
    ) {
        $account = WhatsappAccount::adminOnly()->active()->latest()->first();
        if (!$account || empty($account->session_id)) {
            Log::error('[BotNotification] No active connected admin WhatsApp session available.');
            self::logNotification($eventType, $title, $recipient, $recipientType, $message, 'failed', 'No active admin WhatsApp account connected', 0, null, $metadata);
            return false;
        }

        $cleanRecipient = preg_replace('/[^0-9]/', '', $recipient);
        $isGroup = str_ends_with($recipient, '@g.us') || (str_starts_with($recipient, '120') && strlen($recipient) >= 18);
        $targetJid = $isGroup ? (str_ends_with($recipient, '@g.us') ? $recipient : "{$recipient}@g.us") : "{$cleanRecipient}@s.whatsapp.net";

        try {
            $payload = [
                'sessionId' => $account->session_id,
                'receiver'  => $targetJid,
                'message'   => $message,
                'isGroup'   => $isGroup ? 1 : 0,
                'mediaUrl'  => $mediaUrl,
                'mediaType' => $mediaType,
            ];

            $response = BaileysClient::post('api/messages/send', $payload, 25);
            $resData = $response->json();

            if ($response->successful() && ($resData['success'] ?? false)) {
                self::logNotification($eventType, $title, $recipient, $recipientType, $message, 'sent', null, 0, $account->session_id, $metadata);
                return true;
            }

            $error = $resData['error'] ?? 'Delivery failed';
            self::logNotification($eventType, $title, $recipient, $recipientType, $message, 'failed', $error, 1, $account->session_id, $metadata);
            return false;
        } catch (\Throwable $e) {
            self::logNotification($eventType, $title, $recipient, $recipientType, $message, 'failed', $e->getMessage(), 1, $account->session_id, $metadata);
            return false;
        }
    }

    /**
     * Execute all due scheduled tasks and reminders
     */
    public static function runScheduledTasks()
    {
        $now = Carbon::now();
        $dueTasks = BotScheduledTask::where('status', 1)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $now)
            ->get();

        $processedCount = 0;

        foreach ($dueTasks as $task) {
            $recipients = [];

            if ($task->target_type === 'single') {
                $recipients[] = $task->target_identifier;
            } elseif ($task->target_type === 'group') {
                $recipients[] = $task->target_identifier;
            } elseif ($task->target_type === 'contact_list') {
                $list = ContactList::with('contacts')->find($task->target_identifier);
                if ($list && $list->contacts) {
                    foreach ($list->contacts as $c) {
                        if (!empty($c->phone_number)) {
                            $recipients[] = $c->phone_number;
                        }
                    }
                }
            } elseif ($task->target_type === 'all_contacts') {
                $all = Contact::where('type', 'contact')->pluck('phone_number')->toArray();
                $recipients = array_filter($all);
            }

            $taskSent = 0;
            $taskFailed = 0;

            foreach ($recipients as $target) {
                if (empty($target)) continue;

                $finalMessage = str_replace(
                    ['{name}', '{phone}', '{date}', '{time}'],
                    ['Customer', $target, date('Y-m-d'), date('h:i A')],
                    $task->message
                );

                $sent = self::sendDirectNotification(
                    $target,
                    $finalMessage,
                    $task->media_url,
                    $task->media_type,
                    $task->event_type,
                    $task->title,
                    'customer',
                    ['task_id' => $task->id, 'schedule_type' => $task->schedule_type]
                );

                if ($sent) {
                    $taskSent++;
                } else {
                    $taskFailed++;
                }
            }

            $task->last_run_at = $now;
            $task->total_sent += $taskSent;
            $task->total_failed += $taskFailed;

            // Compute next recurrence
            if ($task->schedule_type === 'once') {
                $task->status = 2; // Completed
                $task->next_run_at = null;
            } else {
                $task->next_run_at = $task->computeNextRunAt();
            }

            $task->save();
            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Create audit log record in bot_notification_logs table
     */
    protected static function logNotification(
        $eventType,
        $title,
        $recipient,
        $recipientType,
        $message,
        $status,
        $errorDetails = null,
        $retryCount = 0,
        $sessionId = null,
        array $metadata = []
    ) {
        try {
            BotNotificationLog::create([
                'event_type'    => $eventType,
                'title'         => $title ?: ucfirst(str_replace('_', ' ', $eventType)),
                'recipient'     => $recipient,
                'recipient_type'=> $recipientType,
                'message'       => $message,
                'status'        => $status,
                'error_details' => $errorDetails,
                'retry_count'   => $retryCount,
                'session_id'    => $sessionId,
                'metadata'      => $metadata,
            ]);
        } catch (\Throwable $e) {
            Log::error('[BotNotification] Failed to write log: ' . $e->getMessage());
        }
    }
}
