<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
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

        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();

        return view('Template::user.plans.index', compact('pageTitle', 'plans', 'activeSubscription', 'currentPlan', 'gatewayCurrency'));
    }

    public function subscribe(Request $request, $id)
    {
        $user = auth()->user();
        $plan = Plan::active()->findOrFail($id);
        $paymentType = $request->payment_type ?? ($plan->price > 0 ? 'gateway' : 'free');

        // Free Plan (1-click trial activation)
        if ($plan->price == 0) {
            UserSubscription::where('user_id', $user->id)->update(['status' => 0]);

            $sub = new UserSubscription();
            $sub->user_id      = $user->id;
            $sub->plan_id      = $plan->id;
            $sub->paid_amount  = 0;
            $sub->starts_at    = now();
            $sub->expires_at   = $plan->duration_days ? now()->addDays($plan->duration_days) : null;
            $sub->status       = 1;
            $sub->save();

            $notify[] = ['success', "Congratulations! You have activated the {$plan->name} trial successfully."];
            return redirect()->route('user.home')->withNotify($notify);
        }

        // Direct Payment via Payment Gateway
        if ($paymentType == 'gateway') {
            $request->validate([
                'gateway'  => 'required',
                'currency' => 'required',
            ]);

            $gate = GatewayCurrency::whereHas('method', function ($g) {
                $g->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

            if (!$gate) {
                $notify[] = ['error', 'Selected payment method is invalid or currently unavailable.'];
                return back()->withNotify($notify);
            }

            if ($gate->min_amount > $plan->price || $gate->max_amount < $plan->price) {
                $notify[] = ['error', 'Plan price is outside the limit for this payment method. Please choose another method.'];
                return back()->withNotify($notify);
            }

            $charge = $gate->fixed_charge + ($plan->price * $gate->percent_charge / 100);
            $payable = $plan->price + $charge;
            $finalAmount = $payable * $gate->rate;

            $deposit = new Deposit();
            $deposit->user_id = $user->id;
            $deposit->account_listing_id = $plan->id;
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
            $deposit->success_url = urlPath('user.home');
            $deposit->failed_url = urlPath('user.plans.index');
            $deposit->save();

            session()->put('Track', $deposit->trx);
            return to_route('user.deposit.confirm');
        }

        // Payment via Wallet Balance
        if ($paymentType == 'balance') {
            if ($user->balance < $plan->price) {
                $notify[] = ['error', 'Insufficient wallet balance. Please select an online payment method to subscribe directly.'];
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

        $notify[] = ['error', 'Invalid payment option selected.'];
        return back()->withNotify($notify);
    }
}
