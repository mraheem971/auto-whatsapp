<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class UserPlanController extends Controller
{
    public function index()
    {
        $pageTitle = 'Subscription Plans & Quotas';
        $user = auth()->user();
        $plans = Plan::active()->get();
        $activeSubscription = $user->activeSubscription;
        $currentPlan = $user->currentPlan();

        return view('Template::user.plans.index', compact('pageTitle', 'plans', 'activeSubscription', 'currentPlan'));
    }

    public function subscribe(Request $request, $id)
    {
        $user = auth()->user();
        $plan = Plan::active()->findOrFail($id);

        if ($plan->price == 0) {
            $alreadyUsedTrial = UserSubscription::where('user_id', $user->id)->where('plan_id', $plan->id)->exists();
            if ($alreadyUsedTrial) {
                $notify[] = ['error', 'You have already utilized your free trial. Please subscribe to a plan to continue.'];
                return back()->withNotify($notify);
            }
        }

        if ($plan->price > 0) {
            if ($user->balance < $plan->price) {
                $notify[] = ['error', 'Insufficient wallet balance. Please deposit funds first.'];
                return redirect()->route('user.deposit.index')->withNotify($notify);
            }

            // Deduct balance
            $user->balance -= $plan->price;
            $user->save();

            // Record transaction
            $trx = new Transaction();
            $trx->user_id      = $user->id;
            $trx->amount       = $plan->price;
            $trx->post_balance = $user->balance;
            $trx->trx_type     = '-';
            $trx->trx          = getTrx();
            $trx->details      = 'Subscribed to plan: ' . $plan->name;
            $trx->remark       = 'plan_subscription';
            $trx->save();
        }

        // Cancel previous active subscriptions
        UserSubscription::where('user_id', $user->id)->update(['status' => 0]);

        // Create new subscription
        $sub = new UserSubscription();
        $sub->user_id      = $user->id;
        $sub->plan_id      = $plan->id;
        $sub->paid_amount  = $plan->price;
        $sub->starts_at    = now();
        $sub->expires_at   = $plan->duration_days ? now()->addDays($plan->duration_days) : null;
        $sub->status       = 1;
        $sub->save();

        try {
            $accounts = \App\Models\WhatsappAccount::where('user_id', $user->id)->get();
            foreach ($accounts as $acc) {
                if ($acc->session_id) {
                    \App\Services\BaileysClient::post('api/autoreply/clear-cache', ['sessionId' => $acc->session_id]);
                }
            }
        } catch (\Throwable $e) {}

        $notify[] = ['success', "Congratulations! You have subscribed to {$plan->name} successfully."];
        return redirect()->route('user.home')->withNotify($notify);
    }
}
