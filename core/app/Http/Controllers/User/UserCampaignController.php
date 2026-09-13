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
    protected $baileysUrl = 'http://127.0.0.1:3000';

    public function index(Request $request)
    {
        $pageTitle = 'Run WhatsApp Marketing Campaigns';
        $user = auth()->user();
        $plan = $user->currentPlan();

        $query = Campaign::where('user_id', $user->id);

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
        $botSettings = UserBotSetting::getSettingsForUser($user->id);

        return view('Template::user.campaigns.create', compact(
            'pageTitle',
            'connectedAccounts',
            'templates',
            'contactLists',
            'totalContacts',
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
        } else {
            $recipientsCount = Contact::where('user_id', $user->id)->where('type', 'contact')->count();
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
        $campaign->min_delay_seconds = $minDelay;
        $campaign->max_delay_seconds = $maxDelay;
        $campaign->status            = 'ready';
        $campaign->total_targets     = $recipientsCount;
        $campaign->sent_count        = 0;
        $campaign->failed_count      = 0;
        $campaign->logs              = [];
        $campaign->save();

        $notify[] = ['success', 'Campaign created successfully! Ready to launch.'];
        return redirect()->route('user.campaigns.view', $campaign->id)->withNotify($notify);
    }

    public function view($id)
    {
        $pageTitle = 'Campaign Execution & Live Delivery';
        $user = auth()->user();
        $campaign = Campaign::where('user_id', $user->id)->findOrFail($id);

        $targets = [];
        if ($campaign->target_type === 'contact_list' && $campaign->contact_list_id) {
            $contacts = Contact::where('user_id', $user->id)->where('contact_list_id', $campaign->contact_list_id)->get();
            foreach ($contacts as $c) {
                $targets[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                ];
            }
        } else {
            $contacts = Contact::where('user_id', $user->id)->where('type', 'contact')->get();
            foreach ($contacts as $c) {
                $targets[] = [
                    'type'       => 'contact',
                    'name'       => $c->name,
                    'target_jid' => $c->target_jid ?: "{$c->phone_number}@s.whatsapp.net",
                    'phone'      => $c->phone_number,
                ];
            }
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

        if (!$targetJid) {
            return response()->json(['success' => false, 'error' => 'Missing target JID'], 400);
        }

        // Replace personalization tags
        $personalizedMessage = str_replace(
            ['{{name}}', '{{phone}}', '@name', '@phone'],
            [$targetName, $targetPhone, $targetName, $targetPhone],
            $campaign->message
        );

        $payload = [
            'sessionId' => $campaign->session_id,
            'recipient' => $targetJid,
            'message'   => $personalizedMessage,
        ];

        if ($campaign->media_url && $campaign->media_type !== 'text') {
            $payload['mediaUrl'] = $campaign->media_url;
            $payload['mediaType'] = $campaign->media_type;
        }

        try {
            $res = Http::timeout(25)->post("{$this->baileysUrl}/api/messages/send", $payload);
            $json = $res->json();

            $isSuccess = ($res->successful() && isset($json['status']) && $json['status'] === 'success');

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
                'sent_count'   => $campaign->fresh()->sent_count,
                'failed_count' => $campaign->fresh()->failed_count,
                'error'        => $isSuccess ? null : ($json['error'] ?? 'Baileys dispatch failed'),
            ]);
        } catch (\Exception $e) {
            $campaign->increment('failed_count');
            return response()->json([
                'success'      => false,
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
