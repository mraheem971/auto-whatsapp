<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $pageTitle = 'WhatsApp SaaS Subscription Plans & Pricing';
        $plans = Plan::withCount(['subscriptions' => function ($q) {
            $q->where('status', 1);
        }])->latest()->get();

        return view('admin.plans.index', compact('pageTitle', 'plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'tagline'          => 'nullable|string|max:255',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1|max:3650',
            'account_limit'    => 'required|integer|min:1|max:1000',
            'autoreply_limit'  => 'required|integer|min:0|max:10000',
            'template_limit'   => 'required|integer|min:0|max:10000',
            'campaign_limit'   => 'required|integer|min:0|max:10000',
            'message_limit'    => 'required|integer|min:0|max:10000000',
            'features'         => 'nullable|array',
            'features.*'       => 'nullable|string|max:255',
        ]);

        $features = array_values(array_filter($request->features ?? [], fn($f) => !empty(trim($f))));

        $plan = new Plan();
        $plan->name            = $request->name;
        $plan->tagline         = $request->tagline;
        $plan->price           = $request->price;
        $plan->duration_days   = $request->duration_days;
        $plan->account_limit   = $request->account_limit;
        $plan->autoreply_limit = $request->autoreply_limit;
        $plan->template_limit  = $request->template_limit;
        $plan->campaign_limit  = $request->campaign_limit;
        $plan->message_limit   = $request->message_limit;
        $plan->features        = $features;
        $plan->is_featured     = $request->has('is_featured') ? 1 : 0;
        $plan->status          = $request->has('status') ? 1 : 0;
        $plan->save();

        $notify[] = ['success', "Plan '{$plan->name}' created successfully!"];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $request->validate([
            'name'             => 'required|string|max:100',
            'tagline'          => 'nullable|string|max:255',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1|max:3650',
            'account_limit'    => 'required|integer|min:1|max:1000',
            'autoreply_limit'  => 'required|integer|min:0|max:10000',
            'template_limit'   => 'required|integer|min:0|max:10000',
            'campaign_limit'   => 'required|integer|min:0|max:10000',
            'message_limit'    => 'required|integer|min:0|max:10000000',
            'features'         => 'nullable|array',
            'features.*'       => 'nullable|string|max:255',
        ]);

        $features = array_values(array_filter($request->features ?? [], fn($f) => !empty(trim($f))));

        $plan->name            = $request->name;
        $plan->tagline         = $request->tagline;
        $plan->price           = $request->price;
        $plan->duration_days   = $request->duration_days;
        $plan->account_limit   = $request->account_limit;
        $plan->autoreply_limit = $request->autoreply_limit;
        $plan->template_limit  = $request->template_limit;
        $plan->campaign_limit  = $request->campaign_limit;
        $plan->message_limit   = $request->message_limit;
        $plan->features        = $features;
        $plan->is_featured     = $request->has('is_featured') ? 1 : 0;
        $plan->status          = $request->has('status') ? 1 : 0;
        $plan->save();

        $notify[] = ['success', "Plan '{$plan->name}' updated successfully!"];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->status = $plan->status == 1 ? 0 : 1;
        $plan->save();

        $statusText = $plan->status == 1 ? 'activated' : 'deactivated';
        $notify[] = ['success', "Plan '{$plan->name}' {$statusText} successfully!"];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $plan = Plan::findOrFail($id);
        $activeCount = UserSubscription::where('plan_id', $plan->id)->where('status', 1)->count();
        if ($activeCount > 0) {
            $notify[] = ['error', "Cannot delete plan '{$plan->name}' because it has {$activeCount} active user subscription(s). You can deactivate it instead."];
            return back()->withNotify($notify);
        }

        $plan->delete();
        $notify[] = ['success', 'Plan deleted successfully!'];
        return back()->withNotify($notify);
    }

    public function subscriptions(Request $request)
    {
        $pageTitle = 'User Plan Subscriptions';
        $query = UserSubscription::with(['user', 'plan'])->latest();

        if ($request->search) {
            $search = trim($request->search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('firstname', 'LIKE', "%{$search}%")
                  ->orWhere('lastname', 'LIKE', "%{$search}%");
            })->orWhereHas('plan', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->status !== null && $request->status !== '') {
            if ($request->status == 'active') {
                $query->where('status', 1)->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            } elseif ($request->status == 'expired') {
                $query->where('expires_at', '<=', now());
            } elseif ($request->status == 'inactive') {
                $query->where('status', 0);
            }
        }

        $subscriptions = $query->paginate(getPaginate());
        $plans = Plan::active()->get();
        $users = User::active()->latest()->take(100)->get();

        return view('admin.plans.subscriptions', compact('pageTitle', 'subscriptions', 'plans', 'users'));
    }

    public function assignSubscription(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'plan_id'       => 'required|exists:plans,id',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'paid_amount'   => 'nullable|numeric|min:0',
        ]);

        $user = User::findOrFail($request->user_id);
        $plan = Plan::findOrFail($request->plan_id);

        $duration = $request->duration_days ?: $plan->duration_days;
        $paidAmount = $request->filled('paid_amount') ? $request->paid_amount : $plan->price;

        // Cancel existing active subscriptions
        UserSubscription::where('user_id', $user->id)->update(['status' => 0]);

        // Create new active subscription
        $sub = new UserSubscription();
        $sub->user_id     = $user->id;
        $sub->plan_id     = $plan->id;
        $sub->paid_amount = $paidAmount;
        $sub->starts_at   = now();
        $sub->expires_at  = $duration ? now()->addDays($duration) : null;
        $sub->status      = 1;
        $sub->save();

        $notify[] = ['success', "Plan '{$plan->name}' successfully assigned to user '{$user->username}' for {$duration} days!"];
        return back()->withNotify($notify);
    }
}

