<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserBotSetting;
use App\Models\WhatsappAccount;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserSettingsController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Settings Hub & Preferences';
        $user = auth()->user();
        $botSettings = UserBotSetting::getSettingsForUser($user->id);
        $whatsappAccounts = WhatsappAccount::where('user_id', $user->id)->get();
        $activeTab = $request->get('tab', 'profile');

        return view('Template::user.settings.index', compact(
            'pageTitle',
            'user',
            'botSettings',
            'whatsappAccounts',
            'activeTab'
        ));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname'  => 'required|string|max:50',
            'mobile'    => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:255',
            'city'      => 'nullable|string|max:100',
            'state'     => 'nullable|string|max:100',
            'zip'       => 'nullable|string|max:30',
            'image'     => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);

        $user = auth()->user();
        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->address   = $request->address;
        $user->city      = $request->city;
        $user->state     = $request->state;
        $user->zip       = $request->zip;

        if ($request->filled('mobile')) {
            $user->mobile = $request->mobile;
        }

        if ($request->hasFile('image')) {
            try {
                $old = $user->image;
                $user->image = fileUploader($request->image, getFilePath('userProfile'), getFileSize('userProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Could not upload your profile image'];
                return back()->withNotify($notify)->withInput();
            }
        }

        $user->save();

        $notify[] = ['success', 'Profile information updated successfully!'];
        return redirect()->route('user.settings.index', ['tab' => 'profile'])->withNotify($notify);
    }

    public function updateBehavior(Request $request)
    {
        $request->validate([
            'min_delay_seconds'       => 'required|integer|min:1|max:120',
            'max_delay_seconds'       => 'required|integer|min:1|max:300|gte:min_delay_seconds',
            'typing_duration_seconds' => 'required|integer|min:1|max:30',
            'daily_send_limit'        => 'required|integer|min:10|max:50000',
            'sleep_start_time'        => 'nullable|string',
            'sleep_end_time'          => 'nullable|string',
        ]);

        $user = auth()->user();
        $settings = UserBotSetting::getSettingsForUser($user->id);
        $settings->min_delay_seconds       = $request->min_delay_seconds;
        $settings->max_delay_seconds       = $request->max_delay_seconds;
        $settings->typing_simulation       = $request->has('typing_simulation');
        $settings->typing_duration_seconds = $request->typing_duration_seconds;
        $settings->recording_simulation    = $request->has('recording_simulation');
        $settings->random_presence_update  = $request->has('random_presence_update');
        $settings->daily_send_limit        = $request->daily_send_limit;
        $settings->sleep_mode              = $request->has('sleep_mode');
        $settings->sleep_start_time        = $request->sleep_start_time ?: '22:00';
        $settings->sleep_end_time          = $request->sleep_end_time ?: '08:00';
        $settings->save();

        $notify[] = ['success', 'Anti-Ban & Human Behavior settings updated successfully!'];
        return redirect()->route('user.settings.index', ['tab' => 'behavior'])->withNotify($notify);
    }

    public function updateBotPreferences(Request $request)
    {
        $request->validate([
            'default_whatsapp_account_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $settings = UserBotSetting::getSettingsForUser($user->id);
        $settings->default_whatsapp_account_id = $request->default_whatsapp_account_id ?? 0;
        $settings->auto_fallback = $request->has('auto_fallback');
        $settings->save();

        $notify[] = ['success', 'WhatsApp Bot & Routing preferences updated!'];
        return redirect()->route('user.settings.index', ['tab' => 'bot'])->withNotify($notify);
    }

    public function updateSecurity(Request $request)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation]
        ]);

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            $notify[] = ['success', 'Account password updated successfully!'];
            return redirect()->route('user.settings.index', ['tab' => 'security'])->withNotify($notify);
        } else {
            $notify[] = ['error', 'Current password is incorrect!'];
            return back()->withNotify($notify);
        }
    }

    public function updateWebhooks(Request $request)
    {
        $request->validate([
            'webhook_url' => 'nullable|url|max:255',
        ]);

        $user = auth()->user();
        $settings = UserBotSetting::getSettingsForUser($user->id);
        $settings->webhook_url = $request->webhook_url;

        if (empty($settings->webhook_secret)) {
            $settings->webhook_secret = 'whsec_' . bin2hex(random_bytes(16));
        }

        $settings->save();

        $notify[] = ['success', 'Inbound Webhook endpoint saved successfully!'];
        return redirect()->route('user.settings.index', ['tab' => 'webhooks'])->withNotify($notify);
    }

    public function regenerateWebhookSecret(Request $request)
    {
        $user = auth()->user();
        $settings = UserBotSetting::getSettingsForUser($user->id);
        $settings->webhook_secret = 'whsec_' . bin2hex(random_bytes(16));
        $settings->save();

        $notify[] = ['success', 'New Webhook signing secret generated!'];
        return redirect()->route('user.settings.index', ['tab' => 'webhooks'])->withNotify($notify);
    }
}

