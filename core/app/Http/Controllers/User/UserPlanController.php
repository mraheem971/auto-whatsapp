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

        $gatewayCurrency = \App\Models\GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', \App\Constants\Status::ENABLE);
        })->with('method')->orderby('name')->get();

        return view('Template::user.plans.index', compact('pageTitle', 'plans', 'activeSubscription', 'currentPlan', 'gatewayCurrency'));
    }

    public function subscribe(Request $request, $id)
    {
        $user = auth()->user();
        $plan = Plan::active()->findOrFail($id);

        // 1. Free Plan
        if ($plan->price <= 0) {
            UserSubscription::where('user_id', $user->id)->update(['status' => 0]);

            $sub = new UserSubscription();
            $sub->user_id      = $user->id;
            $sub->plan_id      = $plan->id;
            $sub->paid_amount  = 0;
            $sub->starts_at    = now();
            $sub->expires_at   = $plan->duration_days ? now()->addDays($plan->duration_days) : null;
            $sub->status       = 1;
            $sub->save();

            $notify[] = ['success', "Congratulations! You have activated the {$plan->name} plan successfully."];
            return redirect()->route('user.home')->withNotify($notify);
        }

        // 2. Direct Online Payment Gateway Checkout
        if ($request->payment_type == 'gateway' || $request->has('gateway')) {
            $request->validate([
                'gateway'  => 'required',
                'currency' => 'required',
            ]);

            $gate = \App\Models\GatewayCurrency::whereHas('method', function ($gate) {
                $gate->where('status', \App\Constants\Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

            if (!$gate) {
                $notify[] = ['error', 'Selected payment gateway is invalid or unavailable.'];
                return back()->withNotify($notify);
            }

            $charge = $gate->fixed_charge + ($plan->price * $gate->percent_charge / 100);
            $payable = $plan->price + $charge;
            $finalAmount = $payable * $gate->rate;

            $deposit = new \App\Models\Deposit();
            $deposit->user_id = $user->id;
            $deposit->account_listing_id = 0;
            $deposit->request_type = 'plan_subscription';
            $deposit->method_code = $gate->method_code;
            $deposit->method_currency = strtoupper($gate->currency);
            $deposit->amount = $plan->price;
            $deposit->charge = $charge;
            $deposit->rate = $gate->rate;
            $deposit->final_amount = $finalAmount;
            $deposit->btc_amount = 0;
            $deposit->btc_wallet = "";
            $deposit->trx = getTrx();
            $deposit->detail = (object)['plan_id' => $plan->id, 'plan_name' => $plan->name];
            $deposit->success_url = route('user.home');
            $deposit->failed_url = route('user.plans.index');
            $deposit->save();

            session()->put('Track', $deposit->trx);
            session()->put('plan_id', $plan->id);

            return redirect()->route('user.deposit.confirm');
        }

        // 3. Wallet Balance Payment
        if ($user->balance < $plan->price) {
            $notify[] = ['error', "Your wallet balance (\${$user->balance}) is insufficient for \${$plan->price}. Please choose an Online Payment Gateway below."];
            return back()->withNotify($notify);
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

        $notify[] = ['success', "Congratulations! You have subscribed to {$plan->name} successfully."];
        return redirect()->route('user.home')->withNotify($notify);
    }
}
