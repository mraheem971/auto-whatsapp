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
            'min_delay_seconds'   => 'nullable|integer|min:1|max:60',
            'max_delay_seconds'   => 'nullable|integer|min:1|max:60',
        ]);

        $botSettings = UserBotSetting::getSettingsForUser($user->id);
        $minDelay = $request->min_delay_seconds ?: $botSettings->min_delay_seconds;
        $maxDelay = $request->max_delay_seconds ?: $botSettings->max_delay_seconds;

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
        $campaign->user_id           = $user->id;
        $campaign->name              = $request->name;
        $campaign->session_id        = $request->session_id;
        $campaign->template_id       = $request->template_id;
        $campaign->contact_list_id   = $listId;
        $campaign->target_type       = $targetType;
        $campaign->message           = $request->message;
        $campaign->media_url         = $request->media_url;
        $campaign->media_type        = $request->media_type ?: 'text';
        $campaign->min_delay         = $minDelay;
        $campaign->max_delay         = $maxDelay;
        $campaign->delay_seconds     = $minDelay;
        $campaign->status            = 'ready';
        $campaign->total_targets     = $recipientsCount;
        $campaign->sent_count        = 0;
        $campaign->failed_count      = 0;
        $campaign->logs              = [];
        if ($request->auto_dispatch || $request->dispatch_mode === 'auto') {
            $campaign->status = 'running';
            $campaign->save();
            \App\Services\CampaignDispatcherService::launchBackgroundProcess($campaign->id);
            $notify[] = ['success', 'Campaign created and launched automatically in the background!'];
        } else {
            $notify[] = ['success', 'Campaign created successfully! Ready to launch.'];
        }

        return redirect()->route('user.campaigns.view', $campaign->id)->withNotify($notify);
    }

    public function startAutoBroadcast($id)
    {
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);
        $campaign->status = 'running';
        $campaign->save();

        \App\Services\CampaignDispatcherService::launchBackgroundProcess($campaign->id);

        return response()->json([
            'success' => true,
            'status'  => 'running',
            'message' => 'Automatic background broadcast started. Running continuously on server.'
        ]);
    }

    public function liveStatus($id)
    {
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);
        $total = $campaign->total_targets ?: 1;
        $processed = $campaign->sent_count + $campaign->failed_count;
        $pct = min(100, round(($processed / $total) * 100));

        return response()->json([
            'success'          => true,
            'status'           => $campaign->status,
            'total_targets'    => $campaign->total_targets,
            'sent_count'       => $campaign->sent_count,
            'failed_count'     => $campaign->failed_count,
            'progress_percent' => $pct,
            'logs'             => array_slice($campaign->logs ?? [], -30),
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
        $campaign->status = $request->status;
        $campaign->save();

        return response()->json(['success' => true, 'status' => $campaign->status]);
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
