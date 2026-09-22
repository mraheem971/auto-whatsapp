<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BotNotificationService;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * Handle Webhook / Internal Events from Baileys Engine
     * POST /api/notifications/event
     */
    public function handleEvent(Request $request)
    {
        $eventType = $request->input('event_type'); // system_error, session_disconnect, incoming_message
        $sessionId = $request->input('session_id');

        if ($eventType === 'incoming_message') {
            $senderPhone = $request->input('sender_phone');
            $messageText = $request->input('message_text');
            $pushName    = $request->input('push_name', '');

            BotNotificationService::handleIncomingMessageAlert(
                $sessionId,
                $senderPhone,
                $messageText,
                $pushName
            );

            return response()->json(['success' => true, 'event' => 'incoming_message_processed']);
        }

        if ($eventType === 'session_connected') {
            if ($sessionId) {
                $acc = \App\Models\WhatsappAccount::where('session_id', $sessionId)->first();
                if ($acc) {
                    $acc->status = 1;
                    if ($request->filled('phone_number')) $acc->phone_number = $request->phone_number;
                    if ($request->filled('profile_name')) $acc->profile_name = $request->profile_name;
                    if ($request->filled('jid')) $acc->jid = $request->jid;
                    $acc->last_connected_at = now();
                    $acc->save();
                }
            }
            return response()->json(['success' => true, 'event' => 'session_connected_recorded']);
        }

        if ($eventType === 'session_disconnect') {
            if ($sessionId) {
                \App\Models\WhatsappAccount::where('session_id', $sessionId)->update(['status' => 0]);
            }
            $title = $request->input('title', 'WhatsApp Session Disconnected');
            $details = $request->input('details', 'WhatsApp session was disconnected or unlinked.');

            BotNotificationService::notifyAdmin(
                $eventType,
                $title,
                $details,
                $request->all()
            );

            return response()->json(['success' => true, 'event' => 'session_disconnect_recorded']);
        }

        if ($eventType === 'system_error') {
            $title = $request->input('title', 'WhatsApp System Error');
            $details = $request->input('details', 'An unexpected error occurred in Baileys microservice.');

            BotNotificationService::notifyAdmin(
                $eventType,
                $title,
                $details,
                $request->all()
            );

            return response()->json(['success' => true, 'event' => 'admin_notified']);
        }

        return response()->json(['success' => false, 'message' => 'Unknown event type'], 400);
    }

    /**
     * Cron Trigger to Run Due Scheduled Tasks
     * GET /api/notifications/cron/run
     */
    public function runScheduledCron()
    {
        $count = BotNotificationService::runScheduledTasks();
        return response()->json([
            'success'   => true,
            'processed' => $count,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
