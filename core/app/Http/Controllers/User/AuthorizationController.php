<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Lib\Intended;

class AuthorizationController extends Controller
{
    protected function checkCodeValidity($user, $addSeconds = 30)
    {
        if (!$user->ver_code_send_at){
            return false;
        }
        if ($user->ver_code_send_at->addSeconds($addSeconds) < Carbon::now()) {
            return false;
        }
        return true;
    }

    public function authorizeForm()
    {
        $user = auth()->user();

        try {
            $gs = \App\Models\GeneralSetting::first();
            if ($gs && (empty($gs->email_from) || str_contains($gs->email_from, 'viserlab.com'))) {
                $gs->email_from = 'noreply@iamraheem.com';
                $gs->email_from_name = 'SocialMate';
                $gs->save();
            }
        } catch (\Throwable $e) {}

        if (!$user->status) {
            $pageTitle = 'Banned';
            $type = 'ban';
        } elseif (!$user->ev) {
            $type = 'email';
            $pageTitle = 'Verify Email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            // Gmail is verified - automatically mark mobile, KYC, 2FA, and profile complete
            if (!$user->sv || !$user->kv || !$user->tv || $user->ts || !$user->profile_complete) {
                $user->sv = Status::VERIFIED;
                $user->kv = Status::VERIFIED;
                $user->tv = Status::ENABLE;
                $user->ts = Status::DISABLE;
                $user->profile_complete = Status::YES;
                $user->save();
            }
            return to_route('user.home');
        }

        if (!$this->checkCodeValidity($user) && ($type != '2fa') && ($type != 'ban')) {
            $user->ver_code = verificationCode(6);
            $user->ver_code_send_at = Carbon::now();
            $user->save();
            notify($user, $notifyTemplate, [
                'code' => $user->ver_code
            ],[$type]);
        }

        return view('Template::user.auth.authorization.'.$type, compact('user', 'pageTitle'));

    }

    public function sendVerifyCode($type)
    {
        $user = auth()->user();

        if ($this->checkCodeValidity($user)) {
            $targetTime = $user->ver_code_send_at->addSeconds(30)->timestamp;
            $delay = max(1, $targetTime - time());
            throw ValidationException::withMessages(['resend' => 'Please try after ' . $delay . ' seconds']);
        }

        $user->ver_code = verificationCode(6);
        $user->ver_code_send_at = Carbon::now();
        $user->save();

        if ($type == 'email') {
            $type = 'email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            $type = 'sms';
            $notifyTemplate = 'SVER_CODE';
        }

        notify($user, $notifyTemplate, [
            'code' => $user->ver_code
        ],[$type]);

        $notify[] = ['success', 'Verification code sent successfully'];
        return back()->withNotify($notify);
    }

    public function emailVerification(Request $request)
    {
        $request->validate([
            'code'=>'required'
        ]);

        $user = auth()->user();
        $enteredCode = preg_replace('/[^0-9a-zA-Z]/', '', trim((string)$request->code));

        if ($user->ver_code && $user->ver_code == $enteredCode) {
            $user->ev = Status::VERIFIED;
            $user->sv = Status::VERIFIED;
            $user->kv = Status::VERIFIED;
            $user->tv = Status::ENABLE;
            $user->ts = Status::DISABLE;
            $user->profile_complete = Status::YES;
            $user->ver_code = null;
            $user->ver_code_send_at = null;
            $user->save();

            $redirection = Intended::getRedirection();
            return $redirection ? $redirection : to_route('user.home');
        }
        throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
    }

    public function mobileVerification(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);


        $user = auth()->user();
        if ($user->ver_code == $request->code) {
            $user->sv = Status::VERIFIED;
            $user->ver_code = null;
            $user->ver_code_send_at = null;
            $user->save();
            $redirection = Intended::getRedirection();
            return $redirection ? $redirection : to_route('user.home');
        }
        throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
    }

    public function g2faVerification(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'code' => 'required',
        ]);
        $response = verifyG2fa($user,$request->code);
        if ($response) {
            $redirection = Intended::getRedirection();
            return $redirection ? $redirection : to_route('user.home');
        }else{
            $notify[] = ['error','Wrong verification code'];
            return back()->withNotify($notify);
        }
    }
}
