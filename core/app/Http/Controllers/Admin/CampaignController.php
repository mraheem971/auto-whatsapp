<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CampaignController extends Controller
{
    protected $baileysUrl;

    public function __construct()
    {
        $this->baileysUrl = rtrim(env('BAILEYS_URL', env('WHATSAPP_SERVER_URL', 'http://127.0.0.1:3000')), '/');
    }

    public function index(Request $request)
    {
        $pageTitle = 'WhatsApp Marketing Campaigns';
        $query = Campaign::query();

        if ($request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('message', 'LIKE', "%$search%");
        }

        $campaigns = $query->latest()->paginate(getPaginate());
        return view('admin.campaign.index', compact('pageTitle', 'campaigns'));
    }

    public function create()
    {
        $pageTitle = 'Create New Campaign';
        $connectedAccounts = WhatsappAccount::adminOnly()->active()->latest()->get();
        $templates = MessageTemplate::latest()->get();
        $contactLists = ContactList::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })->withCount('contacts')->latest()->get();
        
        // Distinct groups from contacts table
        $groups = Contact::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })
            ->whereNotNull('group_id')
            ->where('group_id', '!=', '')
            ->selectRaw('group_name, group_id, count(*) as member_count')
            ->groupBy('group_name', 'group_id')
            ->get();

        $totalContacts = Contact::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })->where('type', 'contact')->count();
        $totalGroups = $groups->count();

        return view('admin.campaign.create', compact('pageTitle', 'connectedAccounts', 'templates', 'contactLists', 'groups', 'totalContacts', 'totalGroups'));
    }

    public function store(Request $request)
    {
        $targetType = $request->target_type;
        $listId = $request->contact_list_id;

        if (str_starts_with($targetType, 'list_')) {
            $listId = (int) str_replace('list_', '', $targetType);
            $targetType = 'contact_list';
            $request->merge(['target_type' => 'contact_list', 'contact_list_id' => $listId]);
        }

        $minDelay = (int) ($request->min_delay ?: 5);
        $maxDelay = (int) ($request->max_delay ?: 15);
        if ($maxDelay < $minDelay) {
            $maxDelay = $minDelay;
        }

        $request->validate([
            'name'             => 'required|string|max:190',
            'session_id'       => 'required|string',
            'target_type'      => 'required|string',
            'contact_list_id'  => 'nullable|integer',
            'target_group_ids' => 'nullable|array',
            'target_group_id'  => 'nullable|string',
            'message'          => 'required|string',
            'min_delay'            => 'nullable|integer|min:1|max:600',
            'max_delay'            => 'nullable|integer|min:1|max:600',
            'daily_limit'          => 'nullable|integer|min:0|max:100000',
            'delay_after_count'    => 'nullable|integer|min:1|max:5000',
            'delay_after_duration' => 'nullable|integer|min:1|max:3600',
            'reset_after_count'    => 'nullable|integer|min:1|max:10000',
        ]);

        // Security check: Admin cannot use user WhatsApp accounts/bots
        $adminAccount = WhatsappAccount::adminOnly()->where('session_id', $request->session_id)->first();
        if (!$adminAccount) {
            $notify[] = ['error', 'Permission denied: Admin cannot use user WhatsApp bots to send campaigns.'];
            return back()->withInput()->withNotify($notify);
        }

        // Calculate recipients list based on target type
        $recipients = [];
        if ($targetType === 'contact_list' || $listId) {
            $contacts = Contact::where('contact_list_id', $listId)->get();
            foreach ($contacts as $c) {
                $recipients[] = [
                    'type'       => $c->type,
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: ($c->type === 'group' ? $c->group_id : "{$c->phone_number}@s.whatsapp.net"),
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                    'group_id'   => $c->group_id,
                ];
            }
        } elseif ($request->target_type === 'groups') {
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();

            foreach ($groups as $g) {
                $recipients[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                    'group_id'   => $g->group_id,
                ];
            }
        } elseif ($request->target_type === 'selected_groups') {
            $selectedIds = $request->target_group_ids ?: [];
            if (empty($selectedIds)) {
                $notify[] = ['error', 'Please select at least one WhatsApp group.'];
                return back()->withInput()->withNotify($notify);
            }

            $groups = Contact::whereIn('group_id', $selectedIds)
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();

            foreach ($groups as $g) {
                $recipients[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                    'group_id'   => $g->group_id,
                ];
            }
        } elseif ($request->target_type === 'selected_group') {
            $group = Contact::where('group_id', $request->target_group_id)->first();
            $recipients[] = [
                'type'       => 'group',
                'name'       => $group ? $group->group_name : 'Selected Group',
                'target_jid' => $request->target_group_id,
                'group_name' => $group ? $group->group_name : 'Selected Group',
                'group_id'   => $request->target_group_id,
            ];
        } elseif ($request->target_type === 'contacts') {
            $contacts = Contact::where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $recipients[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                    'group_id'   => $c->group_id,
                ];
            }
        } else { // 'all'
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $recipients[] = [
                    'type'       => 'group',
                    'name'       => $g->group_name,
                    'target_jid' => $g->group_id,
                    'group_name' => $g->group_name,
                    'group_id'   => $g->group_id,
                ];
            }

            $contacts = Contact::where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $recipients[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                    'group_id'   => $c->group_id,
                ];
            }
        }

        if (empty($recipients)) {
            $notify[] = ['error', 'No recipients found for the selected target audience.'];
            return back()->withInput()->withNotify($notify);
        }

        $campaign = new Campaign();
        $campaign->admin_id         = auth('admin')->id() ?? 1;
        $campaign->name             = $request->name;
        $campaign->session_id       = $request->session_id;
        $campaign->target_type      = $request->target_type;
        $campaign->contact_list_id  = $request->contact_list_id;
        $campaign->target_group_ids = !empty($request->target_group_ids) ? json_encode($request->target_group_ids) : null;
        $campaign->target_group_id  = $request->target_group_id;
        $campaign->message          = $request->message;
        $campaign->min_delay            = $minDelay;
        $campaign->max_delay            = $maxDelay;
        $campaign->delay_seconds        = $minDelay;
        $campaign->delay_after_count    = (int)($request->delay_after_count ?: 50);
        $campaign->delay_after_duration = (int)($request->delay_after_duration ?: 5);
        $campaign->reset_after_count    = (int)($request->reset_after_count ?: 100);
        $campaign->batch_sent_count     = 0;
        $campaign->daily_limit          = ($request->filled('daily_limit') && (int)$request->daily_limit > 0) ? (int)$request->daily_limit : null;
        $campaign->daily_sent_count = 0;
        $campaign->daily_sent_date  = date('Y-m-d');
        $campaign->status           = 'ready';
        $campaign->auto_restart     = $request->has('auto_restart') ? 1 : ($request->auto_restart ?? 1);
        $campaign->loop_count       = 0;
        $campaign->total_targets    = count($recipients);
        $campaign->sent_count       = 0;
        $campaign->failed_count     = 0;
        $campaign->logs             = [];
        if ($request->auto_dispatch || $request->dispatch_mode === 'auto') {
            $campaign->status = 'running';
            $campaign->save();
            \App\Services\CampaignDispatcherService::dispatchNextPendingTarget($campaign->id);
            \App\Services\CampaignDispatcherService::launchBackgroundProcess($campaign->id);
            $notify[] = ['success', 'Campaign created and launched automatically in background!'];
        } else {
            $notify[] = ['success', 'Campaign created successfully! Ready to launch.'];
        }

        return redirect()->route('admin.campaigns.view', $campaign->id)->withNotify($notify);
    }

    public function startAutoBroadcast($id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $campaign->status = 'running';
            // Clear any old next_send_at if it was in the past so it can dispatch immediately
            if ($campaign->next_send_at && now()->gte($campaign->next_send_at)) {
                $campaign->next_send_at = null;
            }
            $campaign->save();

            // Safely launch background process without blocking
            try {
                \App\Services\CampaignDispatcherService::launchBackgroundProcess($campaign->id);
            } catch (\Throwable $e) {
                \Log::warning("Campaign {$id} launchBackgroundProcess warning: " . $e->getMessage());
            }

            // Attempt instant first target dispatch without crashing if network delays occur
            try {
                \App\Services\CampaignDispatcherService::dispatchNextPendingTarget($campaign->id);
            } catch (\Throwable $e) {
                \Log::warning("Campaign {$id} initial dispatch warning: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'status'  => 'running',
                'message' => 'Automatic background broadcast started. Running continuously on server.'
            ]);
        } catch (\Throwable $e) {
            \Log::error("Campaign startAutoBroadcast error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Failed to launch background broadcast: ' . $e->getMessage()
            ], 500);
        }
    }

    public function liveStatus($id)
    {
        $campaign = Campaign::findOrFail($id);

        // If campaign is running, pump next target to ensure continuous real-time delivery
        if ($campaign->status === 'running') {
            \App\Services\CampaignDispatcherService::dispatchNextPendingTarget($campaign->id);
            $campaign = $campaign->fresh();
        }

        $existingLogs = $campaign->logs ?? [];
        $lastResetIndex = -1;
        for ($i = count($existingLogs) - 1; $i >= 0; $i--) {
            if (isset($existingLogs[$i]['type']) && $existingLogs[$i]['type'] === 'cycle_reset') {
                $lastResetIndex = $i;
                break;
            }
        }
        $currentCycleCount = 0;
        $startIndex = ($lastResetIndex >= 0) ? $lastResetIndex + 1 : 0;
        for ($i = $startIndex; $i < count($existingLogs); $i++) {
            if (!empty($existingLogs[$i]['target_jid']) && ($existingLogs[$i]['type'] ?? '') !== 'cycle_reset') {
                $currentCycleCount++;
            }
        }

        $total = $campaign->total_targets ?: 1;
        $pct = min(100, round(($currentCycleCount / $total) * 100));
        if ($pct == 0 && $campaign->sent_count > 0 && $campaign->status === 'running') {
            $pct = min(100, round((($campaign->sent_count % $total) / $total) * 100));
        }

        return response()->json([
            'success'            => true,
            'status'             => $campaign->status,
            'total_targets'      => $campaign->total_targets,
            'sent_count'         => $campaign->sent_count,
            'failed_count'       => $campaign->failed_count,
            'current_round'      => ($campaign->loop_count ?? 0) + 1,
            'loop_count'         => $campaign->loop_count ?? 0,
            'auto_restart'       => (bool)($campaign->auto_restart ?? 1),
            'min_delay'              => $campaign->min_delay_seconds,
            'max_delay'              => $campaign->max_delay_seconds,
            'delay_after_count'      => $campaign->delay_after_count,
            'delay_after_duration'   => $campaign->delay_after_duration,
            'reset_after_count'      => $campaign->reset_after_count,
            'batch_sent_count'       => $campaign->batch_sent_count,
            'seconds_until_next'     => $campaign->seconds_until_next,
            'daily_limit'            => (int)($campaign->daily_limit ?? 0),
            'today_sent_count'       => $campaign->today_sent_count,
            'daily_remaining'        => $campaign->daily_remaining,
            'is_daily_limit_reached' => $campaign->isDailyLimitReached(),
            'progress_percent'       => $pct,
            'logs'                   => array_slice($campaign->logs ?? [], -30),
        ]);
    }

    public function view($id)
    {
        $pageTitle = 'Campaign Execution & Live Delivery';
        $campaign = Campaign::findOrFail($id);

        // Fetch targets list for live execution
        $targets = [];
        $targetType = $campaign->target_type;
        $listId = $campaign->contact_list_id;

        if ($targetType === 'contact_list' || str_starts_with($targetType ?? '', 'list_') || $listId) {
            $effectiveListId = $listId ?: ((str_starts_with($targetType ?? '', 'list_')) ? (int) str_replace('list_', '', $targetType) : null);
            $contacts = Contact::where('contact_list_id', $effectiveListId)->get();

            // If empty, fallback to available contact lists or all admin contacts
            if ($contacts->isEmpty()) {
                $fallbackList = ContactList::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })->first();
                if ($fallbackList) {
                    $contacts = Contact::where('contact_list_id', $fallbackList->id)->get();
                }
                if ($contacts->isEmpty()) {
                    $contacts = Contact::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })->get();
                }
                if ($contacts->isEmpty()) {
                    $contacts = Contact::all();
                }
            }

            foreach ($contacts as $c) {
                $targetJid = $c->target_jid ?: ($c->type === 'group' ? ($c->group_id ?: $c->phone_number) : "{$c->phone_number}@s.whatsapp.net");
                $targets[] = [
                    'type'       => $c->type ?: ($c->group_id ? 'group' : 'contact'),
                    'name'       => $c->name ?: ($c->group_name ?: "+{$c->phone_number}"),
                    'target_jid' => $targetJid,
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        } elseif ($targetType === 'groups') {
            $groups = Contact::whereNotNull('group_id')
                ->where('group_id', '!=', '')
                ->selectRaw('group_name, group_id, MAX(name) as name, MAX(phone_number) as phone_number')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->name ?: ($g->group_name ?: 'WhatsApp Group'),
                    'target_jid' => $g->group_id,
                    'phone'      => $g->phone_number ?: $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($targetType === 'selected_groups') {
            $selectedIds = is_array($campaign->target_group_ids) ? $campaign->target_group_ids : (json_decode($campaign->target_group_ids ?? '[]', true) ?: []);
            $groups = Contact::whereIn('group_id', $selectedIds)
                ->selectRaw('group_name, group_id, MAX(name) as name, MAX(phone_number) as phone_number')
                ->groupBy('group_name', 'group_id')
                ->get();
            foreach ($groups as $g) {
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->name ?: ($g->group_name ?: 'WhatsApp Group'),
                    'target_jid' => $g->group_id,
                    'phone'      => $g->phone_number ?: $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($targetType === 'selected_group') {
            $group = Contact::where('group_id', $campaign->target_group_id)->first();
            $targets[] = [
                'type'       => 'group',
                'name'       => $group ? $group->group_name : 'Selected Group',
                'target_jid' => $campaign->target_group_id,
                'phone'      => $group ? $group->phone_number : $campaign->target_group_id,
                'group_name' => $group ? $group->group_name : 'Selected Group',
            ];
        } elseif ($targetType === 'contacts') {
            $contacts = Contact::where('type', 'contact')->get();
            if ($contacts->isEmpty()) {
                $contacts = Contact::all();
            }
            foreach ($contacts as $c) {
                $targetJid = $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net";
                $targets[] = [
                    'type'       => 'contact',
                    'name'       => $c->name ?: "+{$c->phone_number}",
                    'target_jid' => $targetJid,
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        } else {
            // All contacts & groups
            $contacts = Contact::where(function($q) { $q->whereNull('user_id')->orWhere('user_id', 0); })->get();
            if ($contacts->isEmpty()) {
                $contacts = Contact::all();
            }
            foreach ($contacts as $c) {
                $targetJid = $c->target_jid ?: ($c->type === 'group' ? ($c->group_id ?: $c->phone_number) : "{$c->phone_number}@s.whatsapp.net");
                $targets[] = [
                    'type'       => $c->type ?: ($c->group_id ? 'group' : 'contact'),
                    'name'       => $c->name ?: ($c->group_name ?: "+{$c->phone_number}"),
                    'target_jid' => $targetJid,
                    'phone'      => $c->phone_number,
                    'group_name' => $c->group_name,
                ];
            }
        }

        // Deduplicate targets by target_jid
        $uniqueTargets = [];
        $seenJids = [];
        foreach ($targets as $t) {
            if (!empty($t['target_jid']) && !isset($seenJids[$t['target_jid']])) {
                $seenJids[$t['target_jid']] = true;
                $uniqueTargets[] = $t;
            }
        }
        $targets = $uniqueTargets;

        if ($campaign->total_targets != count($targets) && count($targets) > 0) {
            $campaign->total_targets = count($targets);
            $campaign->save();
        }

        $account = WhatsappAccount::where('session_id', $campaign->session_id)->first();

        return view('admin.campaign.view', compact('pageTitle', 'campaign', 'targets', 'account'));
    }

    public function sendSingle(Request $request, $id)
    {
        $request->validate([
            'target_jid' => 'required|string',
            'name'       => 'nullable|string',
            'type'       => 'nullable|string',
            'group_name' => 'nullable|string',
        ]);

        $campaign = Campaign::findOrFail($id);
        $name = $request->name ?: 'Customer';
        $groupName = $request->group_name ?: '';
        $phone = preg_replace('/[^0-9]/', '', $request->target_jid);

        // Replace shortcodes / personalization tags
        $personalizedMessage = str_replace(
            ['{name}', '{phone}', '{group_name}', '{{name}}', '{{phone}}', '@name', '@phone'],
            [$name, $phone, $groupName, $name, $phone, $name, $phone],
            $campaign->message
        );

        $isGroup = ($request->type === 'group' || str_ends_with($request->target_jid, '@g.us')) ? 1 : 0;

        try {
            $response = \App\Services\BaileysClient::post('api/messages/send', [
                'sessionId' => $campaign->session_id,
                'receiver'  => $request->target_jid,
                'recipient' => $request->target_jid,
                'message'   => $personalizedMessage,
                'isGroup'   => $isGroup,
            ], 25);

            $resData = $response ? $response->json() : [];

            $isSuccess = ($response && $response->successful() && (!empty($resData['success']) || (isset($resData['status']) && $resData['status'] === 'success')));

            $logEntry = [
                'timestamp'  => date('Y-m-d H:i:s'),
                'target'     => $name,
                'target_jid' => $request->target_jid,
                'type'       => $request->type,
            ];

            if ($isSuccess) {
                $campaign->increment('sent_count');
                $logEntry['status'] = 'success';
                $logEntry['message'] = 'Delivered';

                $currentLogs = $campaign->fresh()->logs ?? [];
                $currentLogs[] = $logEntry;
                $campaign->logs = $currentLogs;
                $campaign->save();

                return response()->json([
                    'success'    => true, 
                    'status'     => 'success', 
                    'message'    => 'Delivered',
                    'sent_count' => $campaign->fresh()->sent_count,
                    'failed_count' => $campaign->fresh()->failed_count
                ]);
            }

            $campaign->increment('failed_count');
            $errorMsg = $resData['error'] ?? 'Delivery failed';
            $logEntry['status'] = 'failed';
            $logEntry['error'] = $errorMsg;

            $currentLogs = $campaign->fresh()->logs ?? [];
            $currentLogs[] = $logEntry;
            $campaign->logs = $currentLogs;
            $campaign->save();

            return response()->json([
                'success'      => false, 
                'status'       => 'failed', 
                'error'        => $errorMsg,
                'sent_count'   => $campaign->fresh()->sent_count,
                'failed_count' => $campaign->fresh()->failed_count
            ]);
        } catch (\Exception $e) {
            $campaign->increment('failed_count');
            return response()->json([
                'success'      => false, 
                'status'       => 'failed', 
                'error'        => $e->getMessage(),
                'sent_count'   => $campaign->fresh()->sent_count,
                'failed_count' => $campaign->fresh()->failed_count
            ]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        if ($request->has('status')) {
            $campaign->status = $request->status ?: 'completed';
            if ($campaign->status === 'running') {
                if ($campaign->next_send_at && now()->gte($campaign->next_send_at)) {
                    $campaign->next_send_at = null;
                }
                try {
                    \App\Services\CampaignDispatcherService::launchBackgroundProcess($campaign->id);
                } catch (\Throwable $e) {}
            }
        }
        if ($request->has('auto_restart')) {
            $campaign->auto_restart = $request->auto_restart ? 1 : 0;
        }
        if ($request->has('min_delay')) {
            $campaign->min_delay = max(1, (int)$request->min_delay);
            $campaign->delay_seconds = $campaign->min_delay;
        }
        if ($request->has('max_delay')) {
            $campaign->max_delay = max($campaign->min_delay ?: 1, (int)$request->max_delay);
        }
        if ($request->has('daily_limit')) {
            $campaign->daily_limit = ($request->daily_limit !== null && $request->daily_limit !== '' && (int)$request->daily_limit > 0) ? (int)$request->daily_limit : null;
        }
        if ($request->has('delay_after_count')) {
            $campaign->delay_after_count = max(1, (int)$request->delay_after_count);
        }
        if ($request->has('delay_after_duration')) {
            $campaign->delay_after_duration = max(1, (int)$request->delay_after_duration);
        }
        if ($request->has('reset_after_count')) {
            $campaign->reset_after_count = max(1, (int)$request->reset_after_count);
        }
        $campaign->save();

        return response()->json([
            'success'                => true,
            'status'                 => $campaign->status,
            'auto_restart'           => (bool)$campaign->auto_restart,
            'min_delay'              => $campaign->min_delay_seconds,
            'max_delay'              => $campaign->max_delay_seconds,
            'delay_after_count'      => $campaign->delay_after_count,
            'delay_after_duration'   => $campaign->delay_after_duration,
            'reset_after_count'      => $campaign->reset_after_count,
            'batch_sent_count'       => $campaign->batch_sent_count,
            'seconds_until_next'     => $campaign->seconds_until_next,
            'daily_limit'            => (int)($campaign->daily_limit ?? 0),
            'today_sent_count'       => $campaign->today_sent_count,
            'daily_remaining'        => $campaign->daily_remaining,
            'is_daily_limit_reached' => $campaign->isDailyLimitReached(),
        ]);
    }

    public function delete($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        $notify[] = ['success', 'Campaign deleted successfully'];
        return back()->withNotify($notify);
    }

    public function runCronManual()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('schedule:run');
        } catch (\Exception $e) {}

        $notify[] = ['success', 'Cron job triggered manually on localhost!'];
        return back()->withNotify($notify);
    }
}
