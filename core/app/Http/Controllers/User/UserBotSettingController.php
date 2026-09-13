<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserBotSetting;
use Illuminate\Http\Request;

class UserBotSettingController extends Controller
{
    public function index()
    {
        $pageTitle = 'Anti-Ban & Human Behavior Settings';
        $user = auth()->user();
        $settings = UserBotSetting::getSettingsForUser($user->id);

        return view('Template::user.settings.human_behavior', compact('pageTitle', 'settings'));
    }

    public function update(Request $request)
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

        $notify[] = ['success', 'Anti-Ban & Human Behavior settings saved successfully!'];
        return back()->withNotify($notify);
    }
}
