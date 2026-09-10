<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotNotificationLog;
use App\Models\BotNotificationSetting;
use App\Models\BotScheduledTask;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use App\Services\BotNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BotNotificationController extends Controller
{
    /**
     * Notification Engine Dashboard
     */
    public function index()
    {
        $pageTitle = 'Bot Notification System Dashboard';

        $totalSent = BotNotificationLog::where('status', 'sent')->count();
        $totalFailed = BotNotificationLog::where('status', 'failed')->count();
        $totalEscalations = BotNotificationLog::where('event_type', 'user_error_escalation')->count();
        $totalSystemErrors = BotNotificationLog::where('event_type', 'system_error')->count();
        $activeSchedulesCount = BotScheduledTask::where('status', 1)->count();

        $settings = BotNotificationSetting::getSettings();
        $recentLogs = BotNotificationLog::latest()->take(8)->get();
        $upcomingTasks = BotScheduledTask::where('status', 1)
            ->whereNotNull('next_run_at')
            ->orderBy('next_run_at', 'asc')
            ->take(5)
            ->get();

        $primaryAccount = WhatsappAccount::active()->latest()->first();

        return view('admin.bot_notification.index', compact(
            'pageTitle',
            'totalSent',
            'totalFailed',
            'totalEscalations',
            'totalSystemErrors',
            'activeSchedulesCount',
            'settings',
            'recentLogs',
            'upcomingTasks',
            'primaryAccount'
        ));
    }

    /**
     * Notification Settings & Preferences View
     */
    public function settings()
    {
        $pageTitle = 'Notification Preferences & Admin Alert Settings';
        $settings = BotNotificationSetting::getSettings();
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        $primaryAccount = WhatsappAccount::active()->latest()->first();

        return view('admin.bot_notification.settings', compact('pageTitle', 'settings', 'connectedAccounts', 'primaryAccount'));
    }

    /**
     * Update Notification Settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'admin_whatsapp_number' => 'required|string|max:50',
            'error_reply_message'   => 'nullable|string',
            'error_keywords'        => 'nullable|string',
        ]);

        $settings = BotNotificationSetting::getSettings();
        $settings->admin_whatsapp_number           = preg_replace('/[^0-9]/', '', $request->admin_whatsapp_number);
        $settings->notify_on_system_error          = $request->has('notify_on_system_error');
        $settings->notify_on_user_error_escalation = $request->has('notify_on_user_error_escalation');
        $settings->notify_on_session_disconnect    = $request->has('notify_on_session_disconnect');
        $settings->notify_on_new_message           = $request->has('notify_on_new_message');
        $settings->notify_on_scheduled_reminder    = $request->has('notify_on_scheduled_reminder');
        $settings->auto_error_reply_to_user        = $request->has('auto_error_reply_to_user');
        $settings->error_reply_message             = $request->error_reply_message;
        $settings->error_keywords                  = $request->error_keywords;
        $settings->save();

        $notify[] = ['success', 'Notification preferences saved successfully.'];
        return back()->withNotify($notify);
    }

    /**
     * Send Test Alert to Admin WhatsApp
     */
    public function testAdminAlert(Request $request)
    {
        $settings = BotNotificationSetting::getSettings();
        if (empty($settings->admin_whatsapp_number)) {
            return response()->json(['success' => false, 'message' => 'Please configure an Admin WhatsApp number first.'], 400);
        }

        $sent = BotNotificationService::notifyAdmin(
            'system_error',
            '🔔 Live Test Notification Alert',
            "This is a live test notification from your Auto-WhatsApp Bot Notification Engine.\n\n✅ System Status: Healthy & Online\n⚡ Dispatch Speed: Instant (<1s)\n🔔 Event Alerts: Active",
            ['test' => true, 'triggered_by' => auth('admin')->user()->username ?? 'Admin']
        );

        if ($sent) {
            return response()->json(['success' => true, 'message' => 'Test alert dispatched to +' . $settings->admin_whatsapp_number . ' successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to send test alert. Ensure a WhatsApp account is connected.'], 500);
    }

    /**
     * Task Scheduling & Reminders List
     */
    public function schedules()
    {
        $pageTitle = 'Scheduled Notifications & Reminders';
        $tasks = BotScheduledTask::latest()->paginate(getPaginate());
        $contactLists = ContactList::all();
        $groups = Contact::where('type', 'group')->groupBy('group_name', 'group_id')->select('group_name', 'group_id')->get();
        $connectedAccounts = WhatsappAccount::active()->latest()->get();

        return view('admin.bot_notification.schedules', compact('pageTitle', 'tasks', 'contactLists', 'groups', 'connectedAccounts'));
    }

    /**
     * Store Scheduled Notification Task
     */
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'title'             => 'required|string|max:150',
            'event_type'        => 'required|in:reminder,update,broadcast,custom',
            'schedule_type'     => 'required|in:once,daily,weekly,monthly',
            'target_type'       => 'required|in:single,group,contact_list,all_contacts',
            'target_identifier' => 'nullable|string',
            'message'           => 'required|string',
            'schedule_datetime' => 'nullable|date',
            'schedule_time'     => 'nullable',
            'media_url'         => 'nullable|url',
        ]);

        $task = new BotScheduledTask();
        $task->title                = $request->title;
        $task->event_type           = $request->event_type;
        $task->schedule_type        = $request->schedule_type;
        $task->target_type          = $request->target_type;
        $task->target_identifier    = $request->target_identifier;
        $task->message              = $request->message;
        $task->media_url            = $request->media_url;
        $task->media_type           = $request->media_type ?: 'text';
        $task->session_id           = $request->session_id;
        $task->status               = 1;

        if ($request->schedule_type === 'once') {
            $task->schedule_datetime = $request->schedule_datetime ? Carbon::parse($request->schedule_datetime) : now()->addMinutes(10);
            $task->next_run_at       = $task->schedule_datetime;
        } else {
            $task->schedule_time         = $request->schedule_time ?: '09:00:00';
            $task->schedule_day_of_week  = $request->schedule_day_of_week;
            $task->schedule_day_of_month = $request->schedule_day_of_month;
            $task->next_run_at           = $task->computeNextRunAt();
        }

        $task->save();

        $notify[] = ['success', 'Scheduled task created successfully!'];
        return back()->withNotify($notify);
    }

    /**
     * Toggle Schedule Status (Pause / Active)
     */
    public function toggleScheduleStatus($id)
    {
        $task = BotScheduledTask::findOrFail($id);
        $task->status = ($task->status == 1) ? 0 : 1;
        if ($task->status == 1 && $task->schedule_type !== 'once') {
            $task->next_run_at = $task->computeNextRunAt();
        }
        $task->save();

        $notify[] = ['success', 'Schedule status updated to ' . ($task->status == 1 ? 'Active' : 'Paused')];
        return back()->withNotify($notify);
    }

    /**
     * Run Schedule Task Immediately
     */
    public function runScheduleNow($id)
    {
        $task = BotScheduledTask::findOrFail($id);
        $task->next_run_at = Carbon::now()->subMinute();
        $task->save();

        $processed = BotNotificationService::runScheduledTasks();

        $notify[] = ['success', "Task executed! Dispatched {$processed} scheduled job(s)."];
        return back()->withNotify($notify);
    }

    /**
     * Delete Scheduled Task
     */
    public function deleteSchedule($id)
    {
        $task = BotScheduledTask::findOrFail($id);
        $task->delete();

        $notify[] = ['success', 'Scheduled task deleted.'];
        return back()->withNotify($notify);
    }

    /**
     * Notification Logs & Error Audit History
     */
    public function logs(Request $request)
    {
        $pageTitle = 'Bot Notification Logs & Error Audit';
        $query = BotNotificationLog::latest();

        if ($request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%")
                  ->orWhere('error_details', 'LIKE', "%{$search}%");
            });
        }

        $logs = $query->paginate(getPaginate());

        return view('admin.bot_notification.logs', compact('pageTitle', 'logs'));
    }

    /**
     * Resend Failed Notification
     */
    public function resendLog($id)
    {
        $log = BotNotificationLog::findOrFail($id);
        $sent = BotNotificationService::sendDirectNotification(
            $log->recipient,
            $log->message,
            null,
            'text',
            $log->event_type,
            $log->title,
            $log->recipient_type,
            $log->metadata ?: []
        );

        if ($sent) {
            $log->status = 'sent';
            $log->error_details = null;
            $log->retry_count++;
            $log->save();

            $notify[] = ['success', 'Notification resent successfully!'];
        } else {
            $log->retry_count++;
            $log->save();
            $notify[] = ['error', 'Failed to resend notification. Check WhatsApp connection.'];
        }

        return back()->withNotify($notify);
    }

    /**
     * Clear Notification Logs
     */
    public function clearLogs()
    {
        BotNotificationLog::truncate();
        $notify[] = ['success', 'All notification logs cleared.'];
        return back()->withNotify($notify);
    }
}
