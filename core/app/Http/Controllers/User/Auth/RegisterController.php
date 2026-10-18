<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\User;
use App\Lib\Intended;
use App\Constants\Status;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use App\Models\UserSocialMedia;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\UserNotificationPermission;
use App\Models\Plan;
use App\Models\UserSubscription;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{

    use RegistersUsers;

    public function __construct()
    {
        parent::__construct();
    }

    public function showRegistrationForm()
    {
        $pageTitle = "Register";
        Intended::identifyRoute();
        return view('Template::user.auth.register', compact('pageTitle'));
    }

    protected function validator(array $data)
    {
        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols();
        }

        $agree = 'nullable';
        if (gs('agree')) {
            $agree = 'required';
        }

        $validate = Validator::make($data, [
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|string|email|max:90|unique:users',
            'password'  => ['required', 'confirmed', $passwordValidation],
            'captcha'   => 'sometimes|required',
            'agree'     => $agree
        ], [
            'firstname.required' => 'The first name field is required',
            'lastname.required'  => 'The last name field is required',
            'password.min'       => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        return $validate;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }



        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }


    protected function create(array $data)
    {
        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }

        $email = strtolower(trim($data['email']));

        // Generate clean unique username
        $baseUsername = preg_replace('/[^a-z0-9]/', '', explode('@', $email)[0]);
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }
        $username = $baseUsername . rand(100, 9999);
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . rand(1000, 99999);
        }

        // User Create
        $user                   = new User();
        $user->email            = $email;
        $user->firstname        = trim($data['firstname']);
        $user->lastname         = trim($data['lastname']);
        $user->username         = $username;
        $user->password         = Hash::make($data['password']);
        $user->ref_by           = $referUser ? $referUser->id : 0;
        $user->status           = Status::USER_ACTIVE;
        $user->profile_complete = Status::YES;

        // Auto-verify mobile, KYC, and 2FA upon registration
        $user->kv = Status::YES;
        $user->sv = Status::YES;
        $user->tv = Status::ENABLE;
        $user->ts = Status::DISABLE;

        // Only Gmail (email) requires verification if email verification is enabled
        $user->ev = gs('ev') ? Status::NO : Status::YES;
        $user->save();

        $userSocialMedia = new UserSocialMedia();
        $userSocialMedia->user_id = $user->id;
        $userSocialMedia->save();

        $userNotification = new UserNotificationPermission();
        $userNotification->user_id = $user->id;
        $userNotification->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();

        // Automatically assign free trial / starter plan to newly registered users
        try {
            $trialPlan = Plan::active()
                ->where(function ($q) {
                    $q->where('price', 0)->orWhere('name', 'like', '%trial%')->orWhere('name', 'like', '%free%');
                })
                ->orderBy('price', 'asc')
                ->first();

            // If no free/trial plan exists, find the lowest priced active plan to use as default starter trial
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


        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();

        return $user;
    }

    public function checkUser(Request $request)
    {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data'] = User::where('email', $request->email)->exists();
            $exist['type'] = 'email';
            $exist['field'] = 'Email';
        }
        if ($request->mobile) {
            $exist['data'] = User::where('mobile', $request->mobile)->where('dial_code', $request->mobile_code)->exists();
            $exist['type'] = 'mobile';
            $exist['field'] = 'Mobile';
        }
        if ($request->username) {
            $exist['data'] = User::where('username', $request->username)->exists();
            $exist['type'] = 'username';
            $exist['field'] = 'Username';
        }
        return response($exist);
    }

    public function registered()
    {
        return to_route('user.home');
    }
}
