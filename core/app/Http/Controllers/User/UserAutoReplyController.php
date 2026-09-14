<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AutoReply;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;

class UserAutoReplyController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'My Keyword Bots & Auto-Replies';
        $user = auth()->user();
        $plan = $user->currentPlan();

        $connectedAccounts = WhatsappAccount::where('user_id', $user->id)->active()->latest()->get();
        $contactLists = ContactList::where('user_id', $user->id)->withCount('contacts')->latest()->get();
        $contacts = Contact::where('user_id', $user->id)->where('type', 'contact')->latest()->get();
        $groups = Contact::where('user_id', $user->id)->whereNotNull('group_id')
            ->where('group_id', '!=', '')
            ->selectRaw('group_name, group_id')
            ->groupBy('group_name', 'group_id')
            ->get();

        $query = AutoReply::where('user_id', $user->id)->with(['account', 'contactList'])->latest();

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('keywords', 'LIKE', "%{$search}%")
                  ->orWhere('reply_message', 'LIKE', "%{$search}%");
            });
        }

        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $botRules = $query->paginate(getPaginate());

        $totalBots = AutoReply::where('user_id', $user->id)->count();
        $activeBots = AutoReply::where('user_id', $user->id)->where('status', 1)->count();
        $totalHits = AutoReply::where('user_id', $user->id)->sum('hit_count');

        return view('Template::user.autoreply.index', compact(
            'pageTitle',
            'connectedAccounts',
            'contactLists',
            'contacts',
            'groups',
            'botRules',
            'totalBots',
            'activeBots',
            'totalHits',
            'plan'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $plan = $user->currentPlan();
        $currentCount = AutoReply::where('user_id', $user->id)->count();

        if ($plan && $currentCount >= $plan->autoreply_limit) {
            $notify[] = ['warning', "Auto-reply bot limit reached ({$plan->autoreply_limit}). Please upgrade your plan."];
            return back()->withNotify($notify);
        }

        $request->validate([
            'name'                    => 'required|string|max:150',
            'match_type'              => 'required|in:exact,contains,starts_with,regex,fallback',
            'keywords'                => 'nullable|string',
            'reply_type'              => 'required|in:text,image,video,document,flow',
            'reply_message'           => 'required|string',
            'session_id'              => 'nullable|string',
            'target_type'             => 'required|in:all,all_individual,all_group,saved_contacts,unsaved_contacts,specific_contacts,specific_groups,contact_list',
            'target_contacts'         => 'nullable|string',
            'target_group_ids'        => 'nullable|array',
            'contact_list_id'         => 'nullable|exists:contact_lists,id',
            'read_delay_seconds'      => 'nullable|integer|min:0|max:60',
            'typing_duration_seconds' => 'nullable|integer|min:0|max:60',
            'reply_delay_seconds'     => 'nullable|integer|min:0|max:60',
            'delay_seconds'           => 'nullable|integer|min:0|max:60',
            'cooldown_minutes'        => 'nullable|integer|min:0|max:1440',
        ]);

        $contactsFormatted = null;
        if (!empty($request->target_contacts)) {
            $cArray = array_values(array_filter(array_map(function($p){
                return preg_replace('/[^0-9]/', '', trim($p));
            }, explode(',', $request->target_contacts))));
            $contactsFormatted = json_encode($cArray);
        }

        $bot = new AutoReply();
        $bot->user_id                 = $user->id;
        $bot->name                    = $request->name;
        $bot->match_type              = $request->match_type;
        $bot->keywords                = $request->keywords;
        $bot->reply_type              = $request->reply_type;
        $bot->reply_message           = $request->reply_message;
        $bot->media_url               = $request->media_url;
        $bot->session_id              = $request->session_id;
        $bot->target_type             = $request->target_type;
        $bot->target_contacts         = $contactsFormatted;
        $bot->target_group_ids        = !empty($request->target_group_ids) ? json_encode($request->target_group_ids) : null;
        $bot->contact_list_id         = $request->contact_list_id ?: null;
        $bot->read_delay_seconds      = $request->filled('read_delay_seconds') ? (int)$request->read_delay_seconds : 2;
        $bot->typing_duration_seconds = $request->filled('typing_duration_seconds') ? (int)$request->typing_duration_seconds : 3;
        $bot->reply_delay_seconds     = $request->filled('reply_delay_seconds') ? (int)$request->reply_delay_seconds : ($request->delay_seconds ?: 2);
        $bot->cooldown_minutes        = $request->cooldown_minutes ?: 0;
        $bot->status                  = 1;
        $bot->save();

        try {
            BaileysClient::post('api/autoreply/clear-cache', ['sessionId' => $bot->session_id]);
        } catch (\Throwable $e) {}

        $notify[] = ['success', 'Auto-Reply bot created successfully with custom target audience!'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $bot = AutoReply::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'name'                    => 'required|string|max:150',
            'match_type'              => 'required|in:exact,contains,starts_with,regex,fallback',
            'keywords'                => 'nullable|string',
            'reply_type'              => 'required|in:text,image,video,document,flow',
            'reply_message'           => 'required|string',
            'target_type'             => 'required|in:all,all_individual,all_group,saved_contacts,unsaved_contacts,specific_contacts,specific_groups,contact_list',
            'target_contacts'         => 'nullable|string',
            'target_group_ids'        => 'nullable|array',
            'contact_list_id'         => 'nullable|exists:contact_lists,id',
            'read_delay_seconds'      => 'nullable|integer|min:0|max:60',
            'typing_duration_seconds' => 'nullable|integer|min:0|max:60',
            'reply_delay_seconds'     => 'nullable|integer|min:0|max:60',
        ]);

        $contactsFormatted = null;
        if (!empty($request->target_contacts)) {
            $cArray = array_values(array_filter(array_map(function($p){
                return preg_replace('/[^0-9]/', '', trim($p));
            }, explode(',', $request->target_contacts))));
            $contactsFormatted = json_encode($cArray);
        }

        $bot->name                    = $request->name;
        $bot->match_type              = $request->match_type;
        $bot->keywords                = $request->keywords;
        $bot->reply_type              = $request->reply_type;
        $bot->reply_message           = $request->reply_message;
        $bot->media_url               = $request->media_url;
        $bot->session_id              = $request->session_id;
        $bot->target_type             = $request->target_type;
        $bot->target_contacts         = $contactsFormatted;
        $bot->target_group_ids        = !empty($request->target_group_ids) ? json_encode($request->target_group_ids) : null;
        $bot->contact_list_id         = $request->contact_list_id ?: null;
        $bot->read_delay_seconds      = $request->filled('read_delay_seconds') ? (int)$request->read_delay_seconds : 0;
        $bot->typing_duration_seconds = $request->filled('typing_duration_seconds') ? (int)$request->typing_duration_seconds : 0;
        $bot->reply_delay_seconds     = $request->filled('reply_delay_seconds') ? (int)$request->reply_delay_seconds : ($request->delay_seconds ?: 0);
        $bot->cooldown_minutes        = $request->cooldown_minutes ?: 0;
        $bot->save();

        try {
            BaileysClient::post('api/autoreply/clear-cache', ['sessionId' => $bot->session_id]);
        } catch (\Throwable $e) {}

        $notify[] = ['success', 'Auto-Reply bot updated successfully!'];
        return back()->withNotify($notify);
    }

    public function statusToggle($id)
    {
        $user = auth()->user();
        $bot = AutoReply::where('user_id', $user->id)->findOrFail($id);
        $bot->status = ($bot->status == 1) ? 0 : 1;
        $bot->save();

        try {
            BaileysClient::post('api/autoreply/clear-cache', ['sessionId' => $bot->session_id]);
        } catch (\Throwable $e) {}

        $notify[] = ['success', 'Bot status changed to ' . ($bot->status == 1 ? 'Active' : 'Disabled')];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $user = auth()->user();
        $bot = AutoReply::where('user_id', $user->id)->findOrFail($id);
        $sessionId = $bot->session_id;
        $bot->delete();

        try {
            BaileysClient::post('api/autoreply/clear-cache', ['sessionId' => $sessionId]);
        } catch (\Throwable $e) {}

        $notify[] = ['success', 'Auto-reply bot deleted.'];
        return back()->withNotify($notify);
    }
}
