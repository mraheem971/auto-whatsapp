<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\MessageTemplate;
use App\Models\UserBotSetting;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserCampaignController extends Controller
{
    protected $baileysUrl;

    public function __construct()
    {
        $this->baileysUrl = rtrim(env('BAILEYS_URL', env('WHATSAPP_SERVER_URL', 'http://127.0.0.1:3000')), '/');
    }

    public function index(Request $request)
    {
        $pageTitle = 'Run WhatsApp Marketing Campaigns';
        $user = auth()->user();
        $plan = $user->currentPlan();

        $query = Campaign::where('user_id', $user->id);

        if ($request->status === 'active' || $request->status === 'running') {
            $query->whereIn('status', ['running', 'ready']);
            $pageTitle = 'Active & Running Campaigns';
        } elseif ($request->status === 'pending' || $request->status === 'ready') {
            $query->where('status', 'ready');
            $pageTitle = 'Pending & Scheduled Campaigns';
        } elseif ($request->status === 'completed') {
            $query->where('status', 'completed');
            $pageTitle = 'Completed Campaigns';
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('message', 'LIKE', "%$search%");
            });
        }

        $campaigns = $query->latest()->paginate(getPaginate());
        return view('Template::user.campaigns.index', compact('pageTitle', 'campaigns', 'plan'));
    }

    public function create()
    {
        $pageTitle = 'Run WhatsApp Campaign';
        $user = auth()->user();
        $plan = $user->currentPlan();
        $currentCount = Campaign::where('user_id', $user->id)->count();

        if ($plan && $currentCount >= $plan->campaign_limit) {
            $notify[] = ['warning', "Campaign limit reached ({$plan->campaign_limit}). Please upgrade your plan."];
            return redirect()->route('user.plans.index')->withNotify($notify);
        }

        $connectedAccounts = WhatsappAccount::where('user_id', $user->id)->active()->latest()->get();
        $templates = MessageTemplate::where('user_id', $user->id)->latest()->get();
        $contactLists = ContactList::where('user_id', $user->id)->withCount('contacts')->latest()->get();
        $totalContacts = Contact::where('user_id', $user->id)->where('type', 'contact')->count();
        $totalGroups = Contact::where('user_id', $user->id)->where('type', 'group')->count();
        $totalAll = Contact::where('user_id', $user->id)->count();
        $botSettings = UserBotSetting::getSettingsForUser($user->id);

        return view('Template::user.campaigns.create', compact(
            'pageTitle',
            'connectedAccounts',
            'templates',
            'contactLists',
            'totalContacts',
            'totalGroups',
            'totalAll',
            'botSettings',
            'plan'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $plan = $user->currentPlan();
        $currentCount = Campaign::where('user_id', $user->id)->count();

        if ($plan && $currentCount >= $plan->campaign_limit) {
            $notify[] = ['warning', "Campaign limit reached ({$plan->campaign_limit}). Please upgrade your plan."];
            return back()->withNotify($notify);
        }

        $request->validate([
            'name'                => 'required|string|max:150',
            'session_id'          => 'required|string',
            'target_type'         => 'required|string',
            'message'             => 'required|string',
            'min_delay_seconds'   => 'nullable|integer|min:1|max:600',
            'max_delay_seconds'   => 'nullable|integer|min:1|max:600',
            'delay_after_count'   => 'nullable|integer|min:1|max:5000',
            'delay_after_duration'=> 'nullable|integer|min:1|max:3600',
            'reset_after_count'   => 'nullable|integer|min:1|max:10000',
            'daily_limit'         => 'nullable|integer|min:0|max:100000',
        ]);

        $botSettings = UserBotSetting::getSettingsForUser($user->id);
        $minDelay = (int) ($request->min_delay_seconds ?: ($request->min_delay ?: ($botSettings->min_delay_seconds ?? 30)));
        $maxDelay = (int) ($request->max_delay_seconds ?: ($request->max_delay ?: ($botSettings->max_delay_seconds ?? 60)));
        if ($maxDelay < $minDelay) {
            $maxDelay = $minDelay;
        }

        $targetType = $request->target_type;
        $listId = $request->contact_list_id;

        if (str_starts_with($targetType, 'list_')) {
            $listId = (int) str_replace('list_', '', $targetType);
            $targetType = 'contact_list';
        }

        // Count recipients
        $recipientsCount = 0;
        if ($targetType === 'contact_list' && $listId) {
            $recipientsCount = Contact::where('user_id', $user->id)->where('contact_list_id', $listId)->count();
        } elseif ($targetType === 'groups') {
            $recipientsCount = Contact::where('user_id', $user->id)->where('type', 'group')->count();
        } elseif ($targetType === 'contacts') {
            $recipientsCount = Contact::where('user_id', $user->id)->where('type', 'contact')->count();
        } else {
            $recipientsCount = Contact::where('user_id', $user->id)->count();
        }

        if ($recipientsCount === 0) {
            $recipientsCount = Contact::where('user_id', $user->id)->count();
        }

        $campaign = new Campaign();
        $campaign->user_id              = $user->id;
        $campaign->name                 = $request->name;
        $campaign->session_id           = $request->session_id;
        $campaign->template_id          = $request->template_id;
        $campaign->contact_list_id      = $listId;
        $campaign->target_type          = $targetType;
        $campaign->message              = $request->message;
        $campaign->media_url            = $request->media_url;
        $campaign->media_type           = $request->media_type ?: 'text';
        $campaign->min_delay            = $minDelay;
        $campaign->max_delay            = $maxDelay;
        $campaign->delay_seconds        = $minDelay;
        $campaign->delay_after_count    = $request->filled('delay_after_count') ? (int)$request->delay_after_count : ($botSettings->delay_after_count ?? 50);
        $campaign->delay_after_duration = $request->filled('delay_after_duration') ? (int)$request->delay_after_duration : ($botSettings->delay_after_duration ?? 5);
        $campaign->reset_after_count    = $request->filled('reset_after_count') ? (int)$request->reset_after_count : ($botSettings->reset_after_count ?? 100);
        $campaign->batch_sent_count     = 0;
        $campaign->daily_limit          = ($request->filled('daily_limit') && (int)$request->daily_limit > 0) ? (int)$request->daily_limit : null;
        $campaign->daily_sent_count     = 0;
        $campaign->daily_sent_date      = date('Y-m-d');
        $campaign->status               = 'ready';
        $campaign->auto_restart     = $request->has('auto_restart') ? 1 : ($request->auto_restart ?? 1);
        $campaign->loop_count        = 0;
        $campaign->total_targets     = $recipientsCount;
        $campaign->sent_count        = 0;
        $campaign->failed_count      = 0;
        $campaign->logs              = [];
        if ($request->auto_dispatch || $request->dispatch_mode === 'auto') {
            $campaign->status = 'running';
            $campaign->save();
            \App\Services\CampaignDispatcherService::dispatchNextPendingTarget($campaign->id);
            \App\Services\CampaignDispatcherService::launchBackgroundProcess($campaign->id);
            $notify[] = ['success', 'Campaign created and launched automatically in the background!'];
        } else {
            $notify[] = ['success', 'Campaign created successfully! Ready to launch.'];
        }

        return redirect()->route('user.campaigns.view', $campaign->id)->withNotify($notify);
    }

    public function startAutoBroadcast($id)
    {
        try {
            $user = auth()->user();
            $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);
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
                \Log::warning("User Campaign {$id} launchBackgroundProcess warning: " . $e->getMessage());
            }

            // Attempt instant first target dispatch without crashing if network delays occur
            try {
                \App\Services\CampaignDispatcherService::dispatchNextPendingTarget($campaign->id);
            } catch (\Throwable $e) {
                \Log::warning("User Campaign {$id} initial dispatch warning: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'status'  => 'running',
                'message' => 'Automatic background broadcast started. Running continuously on server.'
            ]);
        } catch (\Throwable $e) {
            \Log::error("User Campaign startAutoBroadcast error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Failed to launch background broadcast: ' . $e->getMessage()
            ], 500);
        }
    }

    public function liveStatus($id)
    {
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);

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
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);

        $targets = [];
        $targetType = $campaign->target_type;
        $listId = $campaign->contact_list_id;

        if ($targetType === 'contact_list' || str_starts_with($targetType ?? '', 'list_') || $listId) {
            $effectiveListId = $listId ?: ((str_starts_with($targetType ?? '', 'list_')) ? (int) str_replace('list_', '', $targetType) : null);
            $contacts = Contact::where('user_id', $user->id)->where('contact_list_id', $effectiveListId)->get();

            if ($contacts->isEmpty()) {
                $fallbackList = ContactList::where('user_id', $user->id)->first();
                if ($fallbackList) {
                    $contacts = Contact::where('user_id', $user->id)->where('contact_list_id', $fallbackList->id)->get();
                }
                if ($contacts->isEmpty()) {
                    $contacts = Contact::where('user_id', $user->id)->get();
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
            $groups = Contact::where('user_id', $user->id)->where('type', 'group')->get();
            if ($groups->isEmpty()) {
                $groups = Contact::where('user_id', $user->id)->whereNotNull('group_id')->where('group_id', '!=', '')->get();
            }
            foreach ($groups as $g) {
                $targetJid = $g->group_id ?: $g->target_jid ?: $g->phone_number;
                $targets[] = [
                    'type'       => 'group',
                    'name'       => $g->name ?: ($g->group_name ?: 'WhatsApp Group'),
                    'target_jid' => $targetJid,
                    'phone'      => $g->phone_number ?: $g->group_id,
                    'group_name' => $g->group_name,
                ];
            }
        } elseif ($targetType === 'contacts') {
            $contacts = Contact::where('user_id', $user->id)->where('type', 'contact')->get();
            if ($contacts->isEmpty()) {
                $contacts = Contact::where('user_id', $user->id)->get();
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
            $contacts = Contact::where('user_id', $user->id)->get();
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

        $botSettings = UserBotSetting::getSettingsForUser($user->id);

        return view('Template::user.campaigns.view', compact('pageTitle', 'campaign', 'targets', 'botSettings'));
    }

    public function sendSingle(Request $request, $id)
    {
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);

        $targetJid = $request->target_jid;
        $targetName = $request->name ?: '';
        $targetPhone = $request->phone ?: '';
        $groupName = $request->group_name ?: '';

        if (!$targetJid) {
            return response()->json(['success' => false, 'error' => 'Missing target JID'], 400);
        }

        // Replace personalization tags
        $personalizedMessage = str_replace(
            ['{{name}}', '{{phone}}', '@name', '@phone', '{name}', '{phone}', '{group_name}'],
            [$targetName, $targetPhone, $targetName, $targetPhone, $targetName, $targetPhone, $groupName],
            $campaign->message
        );

        $isGroup = ($request->type === 'group' || str_ends_with($targetJid, '@g.us')) ? 1 : 0;

        $payload = [
            'sessionId' => $campaign->session_id,
            'receiver'  => $targetJid,
            'recipient' => $targetJid,
            'message'   => $personalizedMessage,
            'isGroup'   => $isGroup,
        ];

        if ($campaign->media_url && $campaign->media_type !== 'text') {
            $payload['mediaUrl'] = $campaign->media_url;
            $payload['mediaType'] = $campaign->media_type;
        }

        try {
            $res = \App\Services\BaileysClient::post('api/messages/send', $payload, 25);
            $json = $res ? $res->json() : [];

            $isSuccess = ($res && $res->successful() && (!empty($json['success']) || (isset($json['status']) && $json['status'] === 'success')));

            if ($isSuccess) {
                $campaign->increment('sent_count');
                $sub = $user->activeSubscription;
                if ($sub) {
                    $sub->increment('messages_sent_count');
                }
            } else {
                $campaign->increment('failed_count');
            }

            return response()->json([
                'success'      => $isSuccess,
                'status'       => $isSuccess ? 'success' : 'failed',
                'sent_count'   => $campaign->fresh()->sent_count,
                'failed_count' => $campaign->fresh()->failed_count,
                'error'        => $isSuccess ? null : ($json['error'] ?? 'Baileys dispatch failed'),
            ]);
        } catch (\Exception $e) {
            $campaign->increment('failed_count');
            return response()->json([
                'success'      => false,
                'status'       => 'failed',
                'sent_count'   => $campaign->fresh()->sent_count,
                'failed_count' => $campaign->fresh()->failed_count,
                'error'        => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);
        if ($request->has('status')) {
            $campaign->status = $request->status;
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
        if ($request->has('delay_after_count')) {
            $campaign->delay_after_count = max(1, (int)$request->delay_after_count);
        }
        if ($request->has('delay_after_duration')) {
            $campaign->delay_after_duration = max(1, (int)$request->delay_after_duration);
        }
        if ($request->has('reset_after_count')) {
            $campaign->reset_after_count = max(1, (int)$request->reset_after_count);
        }
        if ($request->has('daily_limit')) {
            $campaign->daily_limit = ($request->daily_limit !== null && $request->daily_limit !== '' && (int)$request->daily_limit > 0) ? (int)$request->daily_limit : null;
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

    public function saveAntiBanSettings(Request $request)
    {
        $request->validate([
            'min_delay_seconds'    => 'required|integer|min:1|max:600',
            'max_delay_seconds'    => 'required|integer|min:1|max:600|gte:min_delay_seconds',
            'delay_after_count'    => 'required|integer|min:1|max:5000',
            'delay_after_duration' => 'required|integer|min:1|max:3600',
            'reset_after_count'    => 'required|integer|min:1|max:10000',
        ]);

        $user = auth()->user();
        $settings = UserBotSetting::getSettingsForUser($user->id);
        $settings->min_delay_seconds    = (int) $request->min_delay_seconds;
        $settings->max_delay_seconds    = (int) $request->max_delay_seconds;
        $settings->delay_after_count    = (int) $request->delay_after_count;
        $settings->delay_after_duration = (int) $request->delay_after_duration;
        $settings->reset_after_count    = (int) $request->reset_after_count;
        $settings->save();

        return response()->json([
            'success'              => true,
            'message'              => 'Anti-Ban Human Behaviour rules saved successfully!',
            'min_delay_seconds'    => $settings->min_delay_seconds,
            'max_delay_seconds'    => $settings->max_delay_seconds,
            'delay_after_count'    => $settings->delay_after_count,
            'delay_after_duration' => $settings->delay_after_duration,
            'reset_after_count'    => $settings->reset_after_count,
        ]);
    }

    public function delete($id)
    {
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);
        $campaign->delete();

        $notify[] = ['success', 'Campaign deleted.'];
        return back()->withNotify($notify);
    }
}
