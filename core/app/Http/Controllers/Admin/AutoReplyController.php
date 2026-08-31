<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoReply;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;

class AutoReplyController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Auto-Reply & Keyword Bots';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();
        $contactLists = ContactList::withCount('contacts')->latest()->get();
        
        $contacts = Contact::where('type', 'contact')->latest()->get();
        $groups = Contact::whereNotNull('group_id')
            ->where('group_id', '!=', '')
            ->selectRaw('group_name, group_id')
            ->groupBy('group_name', 'group_id')
            ->get();

        $query = AutoReply::with(['account', 'contactList'])->latest();

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('keywords', 'LIKE', "%{$search}%")
                  ->orWhere('reply_message', 'LIKE', "%{$search}%");
            });
        }

        if ($request->session_id) {
            if ($request->session_id === 'all') {
                $query->where(function ($q) {
                    $q->whereNull('session_id')->orWhere('session_id', '');
                });
            } else {
                $query->where('session_id', $request->session_id);
            }
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $botRules = $query->paginate(getPaginate());

        // Stats
        $totalBots = AutoReply::count();
        $activeBots = AutoReply::where('status', 1)->count();
        $totalHits = AutoReply::sum('hit_count');

        return view('admin.autoreply.index', compact(
            'pageTitle',
            'botRules',
            'connectedAccounts',
            'contactLists',
            'contacts',
            'groups',
            'totalBots',
            'activeBots',
            'totalHits'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:150',
            'session_id'              => 'nullable|string|max:100',
            'target_type'             => 'required|string|in:all,all_individual,all_group,saved_contacts,unsaved_contacts,specific_contacts,specific_groups,contact_list',
            'target_contacts'         => 'nullable|string',
            'target_group_ids'        => 'nullable|array',
            'contact_list_id'         => 'nullable|integer',
            'match_type'              => 'required|in:contains,exact,starts_with,ends_with,first_words_2,first_words_3,last_words_2,last_words_3,fallback',
            'keywords'                => 'nullable|string',
            'reply_message'           => 'required|string',
            'read_delay_seconds'      => 'nullable|integer|min:0|max:3600',
            'typing_duration_seconds' => 'nullable|integer|min:0|max:300',
            'reply_delay_seconds'     => 'nullable|integer|min:0|max:3600',
        ]);

        if ($request->match_type !== 'fallback' && empty(trim($request->keywords))) {
            $notify[] = ['error', 'Please provide at least one keyword for this match type.'];
            return back()->withInput()->withNotify($notify);
        }

        // Format keywords into array
        $keywordsFormatted = null;
        if (!empty($request->keywords)) {
            $kwArray = array_values(array_filter(array_map('trim', explode(',', $request->keywords))));
            $keywordsFormatted = json_encode($kwArray);
        }

        // Format target contacts (comma-separated phone numbers)
        $contactsFormatted = null;
        if (!empty($request->target_contacts)) {
            $cArray = array_values(array_filter(array_map(function($p){
                return preg_replace('/[^0-9]/', '', trim($p));
            }, explode(',', $request->target_contacts))));
            $contactsFormatted = json_encode($cArray);
        }

        $bot = new AutoReply();
        $bot->admin_id                = auth('admin')->id() ?? 1;
        $bot->session_id              = !empty($request->session_id) ? $request->session_id : null;
        $bot->name                    = $request->name;
        $bot->chat_scope              = in_array($request->target_type, ['all_group', 'specific_groups']) ? 'group' : 'individual';
        $bot->target_type             = $request->target_type;
        $bot->target_contacts         = $contactsFormatted;
        $bot->target_group_ids        = !empty($request->target_group_ids) ? json_encode($request->target_group_ids) : null;
        $bot->contact_list_id         = $request->contact_list_id ?: null;
        $bot->match_type              = $request->match_type;
        $bot->keywords                = $keywordsFormatted;
        $bot->reply_message           = $request->reply_message;
        $bot->read_delay_seconds      = (int) ($request->read_delay_seconds ?? 0);
        $bot->typing_duration_seconds = (int) ($request->typing_duration_seconds ?? 0);
        $bot->reply_delay_seconds     = (int) ($request->reply_delay_seconds ?? 0);
        $bot->status                  = 1;
        $bot->save();

        $notify[] = ['success', 'Auto-reply bot rule created successfully!'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $bot = AutoReply::findOrFail($id);

        $request->validate([
            'name'                    => 'required|string|max:150',
            'session_id'              => 'nullable|string|max:100',
            'target_type'             => 'required|string|in:all,all_individual,all_group,saved_contacts,unsaved_contacts,specific_contacts,specific_groups,contact_list',
            'target_contacts'         => 'nullable|string',
            'target_group_ids'        => 'nullable|array',
            'contact_list_id'         => 'nullable|integer',
            'match_type'              => 'required|in:contains,exact,starts_with,ends_with,first_words_2,first_words_3,last_words_2,last_words_3,fallback',
            'keywords'                => 'nullable|string',
            'reply_message'           => 'required|string',
            'read_delay_seconds'      => 'nullable|integer|min:0|max:3600',
            'typing_duration_seconds' => 'nullable|integer|min:0|max:300',
            'reply_delay_seconds'     => 'nullable|integer|min:0|max:3600',
            'status'                  => 'nullable|boolean',
        ]);

        if ($request->match_type !== 'fallback' && empty(trim($request->keywords))) {
            $notify[] = ['error', 'Please provide at least one keyword for this match type.'];
            return back()->withInput()->withNotify($notify);
        }

        $keywordsFormatted = null;
        if (!empty($request->keywords)) {
            $kwArray = array_values(array_filter(array_map('trim', explode(',', $request->keywords))));
            $keywordsFormatted = json_encode($kwArray);
        }

        $contactsFormatted = null;
        if (!empty($request->target_contacts)) {
            $cArray = array_values(array_filter(array_map(function($p){
                return preg_replace('/[^0-9]/', '', trim($p));
            }, explode(',', $request->target_contacts))));
            $contactsFormatted = json_encode($cArray);
        }

        $bot->session_id              = !empty($request->session_id) ? $request->session_id : null;
        $bot->name                    = $request->name;
        $bot->chat_scope              = in_array($request->target_type, ['all_group', 'specific_groups']) ? 'group' : 'individual';
        $bot->target_type             = $request->target_type;
        $bot->target_contacts         = $contactsFormatted;
        $bot->target_group_ids        = !empty($request->target_group_ids) ? json_encode($request->target_group_ids) : null;
        $bot->contact_list_id         = $request->contact_list_id ?: null;
        $bot->match_type              = $request->match_type;
        $bot->keywords                = $keywordsFormatted;
        $bot->reply_message           = $request->reply_message;
        $bot->read_delay_seconds      = (int) ($request->read_delay_seconds ?? 0);
        $bot->typing_duration_seconds = (int) ($request->typing_duration_seconds ?? 0);
        $bot->reply_delay_seconds     = (int) ($request->reply_delay_seconds ?? 0);
        $bot->status                  = $request->status ? 1 : 0;
        $bot->save();

        $notify[] = ['success', 'Auto-reply bot rule updated successfully!'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $bot = AutoReply::findOrFail($id);
        $bot->delete();

        $notify[] = ['success', 'Bot rule deleted successfully!'];
        return back()->withNotify($notify);
    }

    public function statusToggle($id)
    {
        $bot = AutoReply::findOrFail($id);
        $bot->status = !$bot->status;
        $bot->save();

        return response()->json([
            'success' => true,
            'status'  => $bot->status,
            'message' => 'Bot status updated to ' . ($bot->status ? 'Active' : 'Inactive')
        ]);
    }

    /**
     * API: Get Active Rules for Baileys Engine with full response flow configs
     */
    public function apiFetchRules($sessionId)
    {
        $rules = AutoReply::active()
            ->forSession($sessionId)
            ->with(['contactList.contacts'])
            ->get();

        $mappedRules = $rules->map(function ($r) {
            $listMemberPhones = [];
            $listGroupIds = [];

            if ($r->contactList && $r->contactList->contacts) {
                foreach ($r->contactList->contacts as $c) {
                    if ($c->phone_number) {
                        $listMemberPhones[] = preg_replace('/[^0-9]/', '', $c->phone_number);
                    }
                    if ($c->group_id) {
                        $listGroupIds[] = $c->group_id;
                    }
                }
            }

            return [
                'id'                      => $r->id,
                'session_id'              => $r->session_id,
                'name'                    => $r->name,
                'chat_scope'              => $r->chat_scope,
                'target_type'             => $r->target_type ?: 'all',
                'target_contacts'         => $r->target_contacts_array,
                'target_group_ids'        => $r->target_group_ids_array,
                'contact_list_id'         => $r->contact_list_id,
                'list_member_phones'      => array_unique($listMemberPhones),
                'list_group_ids'          => array_unique($listGroupIds),
                'match_type'              => $r->match_type,
                'keywords'                => $r->keywords_array,
                'reply_message'           => $r->reply_message,
                'read_delay_seconds'      => (int) ($r->read_delay_seconds ?? 0),
                'typing_duration_seconds' => (int) ($r->typing_duration_seconds ?? 0),
                'reply_delay_seconds'     => (int) ($r->reply_delay_seconds ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'rules'   => $mappedRules
        ]);
    }

    /**
     * API: Log Hit from Baileys Engine
     */
    public function apiLogHit($id)
    {
        $bot = AutoReply::find($id);
        if ($bot) {
            $bot->increment('hit_count');
            return response()->json(['success' => true, 'hit_count' => $bot->hit_count]);
        }
        return response()->json(['success' => false], 404);
    }

    /**
     * Simulate matching against incoming message
     */
    public function simulate(Request $request)
    {
        $text = trim($request->input('text', ''));
        $chatType = $request->input('chat_type', 'individual');
        $sessionId = $request->input('session_id', null);
        $senderPhone = preg_replace('/[^0-9]/', '', $request->input('sender_phone', '923001234567'));
        $targetGroupId = $request->input('group_id', '120363@g.us');
        $isContactSaved = filter_var($request->input('is_saved', true), FILTER_VALIDATE_BOOLEAN);

        if (empty($text)) {
            return response()->json(['matched' => false, 'message' => 'Please provide message text to test.']);
        }

        $rules = AutoReply::active()->forSession($sessionId)->with('contactList.contacts')->get();

        $matchedRule = null;
        $fallbackRule = null;

        $lowerText = mb_strtolower($text);

        // Clean words for word-based matching
        $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $lowerText);
        $cleanText = preg_replace('/\s+/', ' ', trim($cleanText));
        $words = array_values(array_filter(explode(' ', $cleanText)));

        $first2Words = implode(' ', array_slice($words, 0, 2));
        $first3Words = implode(' ', array_slice($words, 0, 3));
        $last2Words  = implode(' ', array_slice($words, -2));
        $last3Words  = implode(' ', array_slice($words, -3));

        foreach ($rules as $r) {
            $targetType = $r->target_type ?: 'all';

            if ($targetType === 'all_individual' && $chatType !== 'individual') continue;
            if ($targetType === 'all_group' && chatType !== 'group') continue;

            if ($targetType === 'saved_contacts') {
                if ($chatType !== 'individual') continue;
                if (!$isContactSaved) continue;
            }

            if ($targetType === 'unsaved_contacts') {
                if ($chatType !== 'individual') continue;
                if ($isContactSaved) continue;
            }

            if ($targetType === 'specific_contacts') {
                if ($chatType !== 'individual') continue;
                $allowedPhones = $r->target_contacts_array;
                if (!in_array($senderPhone, $allowedPhones)) continue;
            }

            if ($targetType === 'specific_groups') {
                if ($chatType !== 'group') continue;
                $allowedGroups = $r->target_group_ids_array;
                if (!in_array($targetGroupId, $allowedGroups)) continue;
            }

            if ($targetType === 'contact_list' && $r->contactList) {
                $inList = false;
                if ($chatType === 'individual') {
                    $inList = $r->contactList->contacts->contains(function($c) use ($senderPhone) {
                        return preg_replace('/[^0-9]/', '', $c->phone_number) === $senderPhone;
                    });
                } else {
                    $inList = $r->contactList->contacts->contains(function($c) use ($targetGroupId) {
                        return $c->group_id === $targetGroupId;
                    });
                }
                if (!$inList) continue;
            }

            if ($r->match_type === 'fallback') {
                $fallbackRule = $r;
                continue;
            }

            $keywords = $r->keywords_array;
            $matched = false;

            foreach ($keywords as $kw) {
                $lowerKw = mb_strtolower(trim($kw));
                if (empty($lowerKw)) continue;

                if ($r->match_type === 'exact' && $lowerText === $lowerKw) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'contains' && str_contains($lowerText, $lowerKw)) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'starts_with' && str_starts_with($lowerText, $lowerKw)) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'ends_with' && str_ends_with($lowerText, $lowerKw)) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'first_words_2' && (str_contains($first2Words, $lowerKw) || in_array($lowerKw, array_slice($words, 0, 2)))) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'first_words_3' && (str_contains($first3Words, $lowerKw) || in_array($lowerKw, array_slice($words, 0, 3)))) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'last_words_2' && (str_contains($last2Words, $lowerKw) || in_array($lowerKw, array_slice($words, -2)))) {
                    $matched = true;
                    break;
                } elseif ($r->match_type === 'last_words_3' && (str_contains($last3Words, $lowerKw) || in_array($lowerKw, array_slice($words, -3)))) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                $matchedRule = $r;
                break;
            }
        }

        $targetRule = $matchedRule ?: $fallbackRule;

        if ($targetRule) {
            $reply = str_replace(
                ['{name}', '{sender_phone}', '{date}', '{time}'],
                ['John Doe', $senderPhone ?: '923001234567', date('Y-m-d'), date('h:i A')],
                $targetRule->reply_message
            );

            return response()->json([
                'matched'                 => true,
                'rule_id'                 => $targetRule->id,
                'rule_name'               => $targetRule->name,
                'match_type'              => $targetRule->match_type,
                'target_type'             => $targetRule->target_type,
                'read_delay_seconds'      => (int) ($targetRule->read_delay_seconds ?? 0),
                'typing_duration_seconds' => (int) ($targetRule->typing_duration_seconds ?? 0),
                'reply_delay_seconds'     => (int) ($targetRule->reply_delay_seconds ?? 0),
                'is_fallback'             => ($targetRule->match_type === 'fallback' && !$matchedRule),
                'reply_output'            => $reply
            ]);
        }

        return response()->json([
            'matched' => false,
            'message' => 'No active bot rule matched this message or sender.'
        ]);
    }
}
