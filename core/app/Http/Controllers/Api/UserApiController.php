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

        // Generate persistent API access token
        $token = 'tok_' . Str::random(40) . '_' . time();
        Cache::put("api_token_{$token}", $user->id, now()->addDays(90));
        Cache::put("user_api_token_{$user->id}", $token, now()->addDays(90));

        // Ensure user has free trial if no active subscription
        if (!$user->hasActiveSubscription()) {
            $this->assignTrialPlanIfEligible($user);
            $user->refresh();
        }

        $plan = $user->currentPlan();
        $sub = $user->activeSubscription;

        $accounts = WhatsappAccount::where('user_id', $user->id)->latest()->get()->map(function ($acc) {
            return [
                'id'           => $acc->id,
                'name'         => $acc->account_name,
                'phone_number' => $acc->phone_number,
                'session_id'   => $acc->session_id,
                'status'       => $acc->status == 1 ? 'connected' : 'disconnected',
                'connected'    => $acc->status == 1,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user'    => [
                'id'          => $user->id,
                'username'    => $user->username ?: ($user->firstname . ' ' . $user->lastname),
                'email'       => $user->email,
                'firstname'   => $user->firstname,
                'lastname'    => $user->lastname,
                'token'       => $token,
                'plan'        => $plan ? $plan->name : 'Free Trial',
                'plan_expiry' => $sub ? ($sub->expires_at ? $sub->expires_at->toIso8601String() : null) : null,
                'has_active_plan' => $user->hasActiveSubscription(),
            ],
            'accounts' => $accounts
        ]);
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
        $sub = $user->fresh()->activeSubscription;

        return response()->json([
            'success' => true,
            'message' => 'Account registered successfully with free trial plan activated!',
            'user'    => [
                'id'          => $user->id,
                'username'    => $user->username ?: ($user->firstname . ' ' . $user->lastname),
                'email'       => $user->email,
                'firstname'   => $user->firstname,
                'lastname'    => $user->lastname,
                'token'       => $token,
                'plan'        => $plan ? $plan->name : 'Free Trial',
                'plan_expiry' => $sub ? ($sub->expires_at ? $sub->expires_at->toIso8601String() : null) : null,
                'has_active_plan' => true,
            ],
            'accounts' => []
        ], 201);
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
    protected function assignTrialPlanIfEligible(User $user)
    {
        try {
            $trialPlan = Plan::active()
                ->where(function ($q) {
                    $q->where('price', 0)->orWhere('name', 'like', '%trial%')->orWhere('name', 'like', '%free%');
                })
                ->orderBy('price', 'asc')
                ->first();

            if (!$trialPlan) {
                $trialPlan = Plan::active()->orderBy('price', 'asc')->first();
            }

            if ($trialPlan) {
                $sub = new UserSubscription();
                $sub->user_id     = $user->id;
                $sub->plan_id     = $trialPlan->id;
                $sub->paid_amount = 0;
                $sub->starts_at   = now();
                $sub->expires_at  = $trialPlan->duration_days ? now()->addDays($trialPlan->duration_days) : now()->addDays(7);
                $sub->status      = 1;
                $sub->save();
            }
        } catch (\Throwable $e) {}
    }
}
