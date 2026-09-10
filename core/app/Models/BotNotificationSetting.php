<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotNotificationSetting extends Model
{
    protected $guarded = ['id'];

    public static function getSettings()
    {
        $settings = self::first();
        if (!$settings) {
            $settings = self::create([
                'admin_whatsapp_number'           => '923216793596',
                'notify_on_new_message'           => false,
                'notify_on_system_error'          => true,
                'notify_on_user_error_escalation' => true,
                'notify_on_session_disconnect'    => true,
                'notify_on_scheduled_reminder'    => true,
                'auto_error_reply_to_user'        => true,
                'error_reply_message'             => "⚠️ Hello {name}, we noticed you encountered an issue. Our support team has been automatically alerted on WhatsApp and will assist you shortly!\n\n📞 Admin Hotline: +923216793596",
                'error_keywords'                  => 'error,issue,problem,not working,kharab,masla,help,admin,complaint,urgent,bug,fail,failed',
            ]);
        }
        return $settings;
    }

    public function getKeywordsArrayAttribute()
    {
        if (empty($this->error_keywords)) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', strtolower($this->error_keywords))));
    }
}
