<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AutoReply;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\MessageTemplate;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\UserNotificationPermission;
use App\Models\UserSocialMedia;
use App\Models\UserSubscription;
use App\Models\WhatsappAccount;
use App\Services\BaileysClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserApiController extends Controller
{
    /**
     * User Login for Android App & REST Clients
     * POST /api/user/login
     * POST /api/login
     */
    public function login(Request $request)
    {
        $login = trim((string)($request->input('login') ?: $request->input('email') ?: $request->input('username')));
        $password = (string)$request->input('password');

        if (empty($login) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide both email/username and password.'
            ], 422);
        }

        $user = User::where('email', strtolower($login))
            ->orWhere('username', $login)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password. Please check your credentials.'
            ], 401);
        }

        if ($user->status == Status::USER_BAN) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.'
            ], 403);
        }

        // Ensure user is fully verified when successfully authenticating via API
        $userUpdated = false;
        if (!$user->ev) {
            $user->ev = Status::YES;
            $userUpdated = true;
        }
        if (!$user->sv) {
            $user->sv = Status::YES;
            $userUpdated = true;
        }
        if (!$user->kv) {
            $user->kv = Status::YES;
            $userUpdated = true;
        }
        if (!$user->profile_complete) {
            $user->profile_complete = Status::YES;
            $userUpdated = true;
        }
        if ($user->ts) {
            $user->ts = Status::DISABLE;
            $userUpdated = true;
        }
        if (!$user->tv) {
            $user->tv = Status::ENABLE;
            $userUpdated = true;
        }
        if ($userUpdated) {
            $user->save();
        }

        // Generate persistent API access token
        $token = 'tok_' . Str::random(40) . '_' . time();
        Cache::put("api_token_{$token}", $user->id, now()->addDays(90));
        Cache::put("user_api_token_{$user->id}", $token, now()->addDays(90));

        // Ensure user has free trial if no active subscription
        if (!$user->hasActiveSubscription()) {
            $this->assignTrialPlanIfEligible($user);
            $user->refresh();
        }

        $fullData = $this->buildUserFullData($user, $token);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Login successful',
        ], $fullData));
    }

    /**
     * User Registration for Android App
     * POST /api/user/register
     * POST /api/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|string|email|max:90|unique:users,email',
            'password'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $username = $request->username ? trim($request->username) : (explode('@', $email)[0] . rand(100, 999));

        $user = new User();
        $user->firstname = trim($request->firstname);
        $user->lastname  = trim($request->lastname);
        $user->username  = $username;
        $user->email     = $email;
        $user->password  = Hash::make($request->password);
        $user->status    = Status::USER_ACTIVE;
        $user->kv        = Status::YES;
        $user->ev        = Status::YES;
        $user->sv        = Status::YES;
        $user->ts        = Status::DISABLE;
        $user->tv        = Status::ENABLE;
        $user->profile_complete = Status::YES;
        $user->save();

        try {
            $userSocialMedia = new UserSocialMedia();
            $userSocialMedia->user_id = $user->id;
            $userSocialMedia->save();

            $userNotification = new UserNotificationPermission();
            $userNotification->user_id = $user->id;
            $userNotification->save();
        } catch (\Throwable $e) {}

        // Automatically activate Free Trial plan
        $this->assignTrialPlanIfEligible($user);

        // Generate persistent API access token
        $token = 'tok_' . Str::random(40) . '_' . time();
        Cache::put("api_token_{$token}", $user->id, now()->addDays(90));
        Cache::put("user_api_token_{$user->id}", $token, now()->addDays(90));

        $plan = $user->fresh()->currentPlan();
        $fullData = $this->buildUserFullData($user, $token);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Account registered successfully with free trial plan activated!',
        ], $fullData), 201);
    }

    /**
     * Get Current User Profile & Subscription Status
     * GET /api/user/profile
     */
    public function profile(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or missing user identifier.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $plan = $user->currentPlan();
        $sub = $user->activeSubscription;

        return response()->json([
            'success' => true,
            'user'    => [
                'id'          => $user->id,
                'username'    => $user->username ?: ($user->firstname . ' ' . $user->lastname),
                'email'       => $user->email,
                'firstname'   => $user->firstname,
                'lastname'    => $user->lastname,
                'plan'        => $plan ? $plan->name : 'Free Trial',
                'plan_expiry' => $sub ? ($sub->expires_at ? $sub->expires_at->toIso8601String() : null) : null,
                'has_active_plan' => $user->hasActiveSubscription(),
                'accounts_count'  => WhatsappAccount::where('user_id', $user->id)->count(),
                'active_accounts' => WhatsappAccount::where('user_id', $user->id)->where('status', 1)->count(),
            ]
        ]);
    }

    /**
     * List user's WhatsApp Accounts
     * GET /api/user/accounts
     */
    public function accounts(Request $request)
    {
        $userId = $this->resolveUserId($request);
        $query = WhatsappAccount::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $accounts = $query->latest()->get()->map(function ($acc) {
            return [
                'id'           => (string)$acc->id,
                'name'         => $acc->account_name ?: 'WhatsApp Account',
                'phone_number' => $acc->phone_number ?: '',
                'session_id'   => $acc->session_id,
                'status'       => $acc->status == 1 ? 'connected' : 'disconnected',
                'connected'    => $acc->status == 1,
                'battery'      => 100,
            ];
        });

        return response()->json([
            'success'  => true,
            'count'    => $accounts->count(),
            'accounts' => $accounts
        ]);
    }

    /**
     * Delete user's WhatsApp Account
     * POST /api/user/accounts/delete/{id}
     */
    public function deleteAccount(Request $request, $id)
    {
        $userId = $this->resolveUserId($request);
        $query = WhatsappAccount::where('id', $id);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $account = $query->first();

        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        try {
            BaileysClient::post("api/sessions/delete/{$account->session_id}", [], 5);
        } catch (\Throwable $e) {}

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp account deleted successfully.'
        ]);
    }

    /**
     * Helper to resolve user ID from bearer token, query param, or header
     */
    protected function resolveUserId(Request $request): ?int
    {
        if ($request->filled('user_id')) {
            return (int)$request->input('user_id');
        }
        if ($request->filled('userId')) {
            return (int)$request->input('userId');
        }
        if ($request->header('X-User-Id')) {
            return (int)$request->header('X-User-Id');
        }

        $bearer = $request->bearerToken();
        if ($bearer) {
            $cached = Cache::get("api_token_{$bearer}");
            if ($cached) return (int)$cached;
        }

        return null;
    }

    /**
     * Assign trial plan automatically
     */
    /**
     * Comprehensive synchronization endpoint for mobile app
     * GET /api/user/sync-all
     * POST /api/user/sync-all
     */
    public function syncAll(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or missing user identifier.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $fullData = $this->buildUserFullData($user);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Data synced successfully',
        ], $fullData));
    }

    /**
     * Get user's auto-reply rules
     * GET /api/user/auto-replies
     */
    public function autoReplies(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $fullData = $this->buildUserFullData($user);

        return response()->json([
            'success' => true,
            'rules'   => $fullData['auto_replies'],
            'count'   => count($fullData['auto_replies']),
        ]);
    }

    /**
     * Save/Create Auto-Reply from mobile app
     * POST /api/user/auto-replies/save
     */
    public function saveAutoReply(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $id = $request->input('id');
        $rule = null;
        if ($id && is_numeric($id)) {
            $rule = AutoReply::where('id', $id)->where('user_id', $userId)->first();
        }

        if (!$rule) {
            $rule = new AutoReply();
            $rule->user_id = $userId;
        }

        $rule->name = $request->input('name') ?: 'Android Bot Rule';
        $rule->match_type = strtolower($request->input('triggerType') ?: ($request->input('match_type') ?: 'contains'));
        $rule->keywords = $request->input('keyword') ?: ($request->input('keywords') ?: '');
        $rule->reply_text = $request->input('replyMessage') ?: ($request->input('reply_text') ?: '');
        $rule->status = $request->has('isActive') ? ($request->input('isActive') ? 1 : 0) : 1;
        $rule->target_audience = strtolower($request->input('targetAudience') ?: 'all');
        $rule->reply_delay_seconds = (int)$request->input('delaySeconds', 3);
        $rule->read_delay_seconds = (int)$request->input('seenDelaySeconds', 2);
        $rule->typing_duration_seconds = (int)$request->input('typingDelaySeconds', 3);
        $rule->save();

        return response()->json([
            'success' => true,
            'message' => 'Auto-reply rule saved successfully.',
            'rule'    => $rule,
        ]);
    }

    /**
     * Delete Auto-Reply from mobile app
     * POST /api/user/auto-replies/delete/{id}
     */
    public function deleteAutoReply(Request $request, $id)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $rule = AutoReply::where('id', $id)->where('user_id', $userId)->first();
        if (!$rule) {
            return response()->json(['success' => false, 'message' => 'Rule not found.'], 404);
        }

        $rule->delete();
        return response()->json(['success' => true, 'message' => 'Rule deleted successfully.']);
    }

    /**
     * Get user's message templates
     * GET /api/user/templates
     */
    public function templates(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $fullData = $this->buildUserFullData($user);

        return response()->json([
            'success'   => true,
            'templates' => $fullData['templates'],
            'count'     => count($fullData['templates']),
        ]);
    }

    /**
     * Save/Create Message Template from mobile app
     * POST /api/user/templates/save
     */
    public function saveTemplate(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $id = $request->input('id');
        $tpl = null;
        if ($id && is_numeric($id)) {
            $tpl = MessageTemplate::where('id', $id)->where('user_id', $userId)->first();
        }

        if (!$tpl) {
            $tpl = new MessageTemplate();
            $tpl->user_id = $userId;
        }

        $tpl->title = $request->input('title') ?: ($request->input('name') ?: 'Template');
        $tpl->content = $request->input('bodyText') ?: ($request->input('content') ?: '');
        $tpl->category = $request->input('category') ?: 'marketing';
        $tpl->save();

        return response()->json([
            'success'  => true,
            'message'  => 'Template saved successfully.',
            'template' => $tpl,
        ]);
    }

    /**
     * Delete Message Template from mobile app
     * POST /api/user/templates/delete/{id}
     */
    public function deleteTemplate(Request $request, $id)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $tpl = MessageTemplate::where('id', $id)->where('user_id', $userId)->first();
        if (!$tpl) {
            return response()->json(['success' => false, 'message' => 'Template not found.'], 404);
        }

        $tpl->delete();
        return response()->json(['success' => true, 'message' => 'Template deleted successfully.']);
    }

    /**
     * Get user's campaigns
     * GET /api/user/campaigns
     */
    public function campaigns(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $fullData = $this->buildUserFullData($user);

        return response()->json([
            'success'   => true,
            'campaigns' => $fullData['campaigns'],
            'count'     => count($fullData['campaigns']),
        ]);
    }

    /**
     * Get user's contacts
     * GET /api/user/contacts
     */
    public function contacts(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $fullData = $this->buildUserFullData($user);

        return response()->json([
            'success'  => true,
            'contacts' => $fullData['contacts'],
            'count'    => count($fullData['contacts']),
        ]);
    }

    /**
     * Helper to construct all user information and data added on web
     */
    protected function buildUserFullData(User $user, ?string $token = null): array
    {
        $plan = $user->currentPlan();
        $sub = $user->activeSubscription;

        $accounts = WhatsappAccount::where('user_id', $user->id)->latest()->get()->map(function ($acc) {
            return [
                'id'           => (string)$acc->id,
                'name'         => $acc->account_name ?: 'WhatsApp Account',
                'phone_number' => $acc->phone_number ?: '',
                'session_id'   => $acc->session_id,
                'status'       => $acc->status == 1 ? 'connected' : 'disconnected',
                'connected'    => $acc->status == 1,
                'battery'      => 100,
            ];
        });

        $autoReplies = AutoReply::where('user_id', $user->id)->latest()->get()->map(function ($r) {
            $triggerType = strtoupper($r->match_type ?? 'CONTAINS');
            if ($triggerType == 'EXACT') $triggerType = 'EXACT';
            elseif ($triggerType == 'STARTS_WITH') $triggerType = 'STARTS_WITH';
            elseif ($triggerType == 'FALLBACK') $triggerType = 'FALLBACK';
            else $triggerType = 'CONTAINS';

            $audience = 'ALL';
            if ($r->target_audience == 'direct') $audience = 'DIRECT_ONLY';
            elseif ($r->target_audience == 'groups') $audience = 'GROUPS_ONLY';

            return [
                'id'                 => (string)$r->id,
                'name'               => $r->name ?: ('Bot #' . $r->id),
                'triggerType'        => $triggerType,
                'keyword'            => is_array($r->keywords_array) ? implode(', ', $r->keywords_array) : ($r->keywords ?: ''),
                'replyMessage'       => $r->reply_text ?: '',
                'targetAudience'     => $audience,
                'isActive'           => (bool)$r->status,
                'triggerCount'       => (int)$r->hit_count,
                'delaySeconds'       => (int)($r->reply_delay_seconds ?: 3),
                'seenDelaySeconds'   => (int)($r->read_delay_seconds ?: 2),
                'typingDelaySeconds' => (int)($r->typing_duration_seconds ?: 3),
                'sendDelaySeconds'   => (int)($r->reply_delay_seconds ?: 4),
                'randomizeDelay'     => true,
            ];
        });

        $templates = MessageTemplate::where('user_id', $user->id)->latest()->get()->map(function ($t) {
            return [
                'id'         => (string)$t->id,
                'title'      => $t->name ?: ($t->title ?: 'Template #' . $t->id),
                'category'   => strtoupper($t->type ?: ($t->category ?: 'MARKETING')),
                'headerText' => '',
                'bodyText'   => $t->content ?: ($t->message ?: ''),
                'footerText' => '',
                'mediaType'  => 'NONE',
                'mediaUrl'   => null,
            ];
        });

        $campaigns = Campaign::where('user_id', $user->id)->latest()->get()->map(function ($c) {
            $status = 'DRAFT';
            if ($c->status == 1 || $c->status == 'running') $status = 'RUNNING';
            elseif ($c->status == 2 || $c->status == 'completed') $status = 'COMPLETED';
            elseif ($c->status == 3 || $c->status == 'paused') $status = 'PAUSED';

            return [
                'id'             => (string)$c->id,
                'name'           => $c->name ?: ('Campaign #' . $c->id),
                'templateId'     => (string)($c->template_id ?? ''),
                'templateTitle'  => $c->name ?? '',
                'targetGroup'    => 'ALL',
                'targetType'     => 'ALL_CONTACTS',
                'totalCount'     => (int)($c->total_contacts ?? ($c->total_count ?? 0)),
                'sentCount'      => (int)($c->sent_count ?? 0),
                'deliveredCount' => (int)($c->delivered_count ?? 0),
                'failedCount'    => (int)($c->failed_count ?? 0),
                'status'         => $status,
                'delaySeconds'   => (int)($c->min_delay_seconds ?? 5),
                'maxDelaySeconds'=> (int)($c->max_delay_seconds ?? 15),
                'randomJitter'   => true,
                'batchSize'      => 20,
                'cooldownSeconds'=> 45,
                'enableSpintax'  => true,
                'dailyLimit'     => 500,
            ];
        });

        $contacts = Contact::where('user_id', $user->id)->latest()->take(500)->get()->map(function ($ct) {
            return [
                'id'          => (string)$ct->id,
                'name'        => $ct->name ?: 'Contact',
                'phoneNumber' => $ct->phone_number ?: ($ct->whatsapp_number ?: ($ct->mobile ?: '')),
                'groupTag'    => $ct->group_name ?: 'General',
                'notes'       => '',
                'avatarColor' => -16744448,
            ];
        });

        $userData = [
            'id'          => $user->id,
            'username'    => $user->username ?: ($user->firstname . ' ' . $user->lastname),
            'email'       => $user->email,
            'firstname'   => $user->firstname,
            'lastname'    => $user->lastname,
            'token'       => $token ?: Cache::get("user_api_token_{$user->id}", 'tok_' . $user->id),
            'plan'        => $plan ? $plan->name : 'Free Trial',
            'plan_expiry' => $sub ? ($sub->expires_at ? $sub->expires_at->toIso8601String() : null) : null,
            'has_active_plan' => $user->hasActiveSubscription(),
        ];

        return [
            'user'         => $userData,
            'accounts'     => $accounts,
            'auto_replies' => $autoReplies,
            'templates'    => $templates,
            'campaigns'    => $campaigns,
            'contacts'     => $contacts,
            'stats'        => [
                'accounts_count'  => $accounts->count(),
                'active_accounts' => $accounts->where('connected', true)->count(),
                'rules_count'     => $autoReplies->count(),
                'active_rules'    => $autoReplies->where('isActive', true)->count(),
                'templates_count' => $templates->count(),
                'campaigns_count' => $campaigns->count(),
                'contacts_count'  => $contacts->count(),
            ]
        ];
    }
}
