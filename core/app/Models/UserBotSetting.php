<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBotSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'typing_simulation' => 'boolean',
        'recording_simulation' => 'boolean',
        'random_presence_update' => 'boolean',
        'sleep_mode' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getSettingsForUser($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'min_delay_seconds' => 5,
                'max_delay_seconds' => 15,
                'typing_simulation' => 1,
                'typing_duration_seconds' => 3,
                'recording_simulation' => 0,
                'random_presence_update' => 1,
                'daily_send_limit' => 500,
                'sleep_mode' => 0,
                'sleep_start_time' => '22:00',
                'sleep_end_time' => '08:00',
            ]
        );
    }
}
