<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoReply;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;

class AutoReplyController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Auto-Reply & Keyword Bots';
        $connectedAccounts = WhatsappAccount::active()->latest()->get();

        $query = AutoReply::with('account')->latest();

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
            'totalBots',
            'activeBots',
            'totalHits'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'session_id'       => 'nullable|string|max:100',
            'chat_scope'       => 'required|in:all,individual,group',
            'match_type'       => 'required|in:contains,exact,starts_with,ends_with,fallback',
            'keywords'         => 'nullable|string',
            'reply_message'    => 'required|string',
            'status'           => 'nullable|boolean',
            'cooldown_seconds' => 'nullable|integer|min:0|max:86400',
        ]);

        if ($request->match_type !== 'fallback' && empty(trim($request->keywords))) {
            $notify[] = ['error', 'Please provide at least one keyword for this match type.'];
            return back()->withInput()->withNotify($notify);
        }

        // Clean and format keywords into array
        $keywordsFormatted = null;
        if (!empty($request->keywords)) {
            $kwArray = array_values(array_filter(array_map('trim', explode(',', $request->keywords))));
            $keywordsFormatted = json_encode($kwArray);
        }

        $bot = new AutoReply();
        $bot->admin_id         = auth('admin')->id() ?? 1;
        $bot->session_id       = !empty($request->session_id) ? $request->session_id : null;
        $bot->name             = $request->name;
        $bot->chat_scope       = $request->chat_scope;
        $bot->match_type       = $request->match_type;
        $bot->keywords         = $keywordsFormatted;
        $bot->reply_message    = $request->reply_message;
        $bot->status           = $request->has('status') ? 1 : 1;
        $bot->cooldown_seconds = (int) ($request->cooldown_seconds ?? 0);
        $bot->save();

        $notify[] = ['success', 'Auto-reply bot rule created successfully!'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $bot = AutoReply::findOrFail($id);

        $request->validate([
            'name'             => 'required|string|max:150',
            'session_id'       => 'nullable|string|max:100',
            'chat_scope'       => 'required|in:all,individual,group',
            'match_type'       => 'required|in:contains,exact,starts_with,ends_with,fallback',
            'keywords'         => 'nullable|string',
            'reply_message'    => 'required|string',
            'status'           => 'nullable|boolean',
            'cooldown_seconds' => 'nullable|integer|min:0|max:86400',
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

        $bot->session_id       = !empty($request->session_id) ? $request->session_id : null;
        $bot->name             = $request->name;
        $bot->chat_scope       = $request->chat_scope;
        $bot->match_type       = $request->match_type;
        $bot->keywords         = $keywordsFormatted;
        $bot->reply_message    = $request->reply_message;
        $bot->status           = $request->status ? 1 : 0;
        $bot->cooldown_seconds = (int) ($request->cooldown_seconds ?? 0);
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
     * API: Get Active Rules for Baileys Engine
     */
    public function apiFetchRules($sessionId)
    {
        $rules = AutoReply::active()
            ->forSession($sessionId)
            ->get(['id', 'session_id', 'name', 'chat_scope', 'match_type', 'keywords', 'reply_message', 'cooldown_seconds']);

        return response()->json([
            'success' => true,
            'rules'   => $rules
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
        $chatType = $request->input('chat_type', 'individual'); // individual or group
        $sessionId = $request->input('session_id', null);

        if (empty($text)) {
            return response()->json(['matched' => false, 'message' => 'Please provide message text to test.']);
        }

        $rules = AutoReply::active()->forSession($sessionId)->get();

        $matchedRule = null;
        $fallbackRule = null;

        $lowerText = mb_strtolower($text);

        foreach ($rules as $r) {
            // Check chat scope
            if ($r->chat_scope !== 'all' && $r->chat_scope !== $chatType) {
                continue;
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

                if ($r->match_type === 'exact') {
                    if ($lowerText === $lowerKw) {
                        $matched = true;
                        break;
                    }
                } elseif ($r->match_type === 'contains') {
                    if (str_contains($lowerText, $lowerKw)) {
                        $matched = true;
                        break;
                    }
                } elseif ($r->match_type === 'starts_with') {
                    if (str_starts_with($lowerText, $lowerKw)) {
                        $matched = true;
                        break;
                    }
                } elseif ($r->match_type === 'ends_with') {
                    if (str_ends_with($lowerText, $lowerKw)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $matchedRule = $r;
                break;
            }
        }

        $targetRule = $matchedRule ?: $fallbackRule;

        if ($targetRule) {
            // Variable substitution preview
            $reply = str_replace(
                ['{name}', '{sender_phone}', '{date}', '{time}'],
                ['John Doe', '923001234567', date('Y-m-d'), date('h:i A')],
                $targetRule->reply_message
            );

            return response()->json([
                'matched'      => true,
                'rule_id'      => $targetRule->id,
                'rule_name'    => $targetRule->name,
                'match_type'   => $targetRule->match_type,
                'is_fallback'  => ($targetRule->match_type === 'fallback' && !$matchedRule),
                'reply_output' => $reply
            ]);
        }

        return response()->json([
            'matched' => false,
            'message' => 'No active bot rule matched this message.'
        ]);
    }
}
