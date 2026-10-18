<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = auth()->user();

            // When email (Gmail) is verified, ensure mobile, KYC, and profile completion are automatically verified
            if ($user->ev) {
                if (!$user->sv || !$user->kv || !$user->profile_complete) {
                    $user->sv = 1;
                    $user->kv = 1;
                    $user->profile_complete = 1;
                    $user->save();
                }
            }

            $is2faActive = ($user->ts && !empty($user->tsc));
            $is2faVerified = $is2faActive ? $user->tv : true;

            if ($user->status && $user->ev && $user->sv && $is2faVerified) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You need to verify your account first.';
                    return response()->json([
                        'remark'=>'unverified',
                        'status'=>'error',
                        'message'=>['error'=>$notify],
                        'data'=>[
                            'user'=>$user
                        ],
                    ]);
                }else{
                    return to_route('user.authorization');
                }
            }
        }
        abort(403);
    }
}
