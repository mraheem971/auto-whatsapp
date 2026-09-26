<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'logs'            => 'array',
        'next_send_at'    => 'datetime',
        'daily_sent_date' => 'date',
    ];

    public function getSecondsUntilNextAttribute()
    {
        if (!$this->next_send_at) {
            return 0;
        }
        return max(0, now()->diffInSeconds($this->next_send_at, false));
    }

    public function getMinDelaySecondsAttribute()
    {
        return (int) ($this->attributes['min_delay'] ?? ($this->attributes['delay_seconds'] ?? 5));
    }

    public function getMaxDelaySecondsAttribute()
    {
        return (int) ($this->attributes['max_delay'] ?? ($this->attributes['delay_seconds'] ? ($this->attributes['delay_seconds'] + 5) : 15));
    }

    public function getDailyLimitAttribute($value)
    {
        return (int) ($value ?? 0);
    }

    public function getTodaySentCountAttribute(): int
    {
        $today = date('Y-m-d');
        $sentDate = $this->daily_sent_date ? (is_string($this->daily_sent_date) ? substr($this->daily_sent_date, 0, 10) : $this->daily_sent_date->format('Y-m-d')) : null;
        if ($sentDate !== $today) {
            return 0;
        }
        return (int) ($this->attributes['daily_sent_count'] ?? 0);
    }

    public function isDailyLimitReached(): bool
    {
        $limit = $this->daily_limit;
        if ($limit <= 0) {
            return false;
        }
        return $this->today_sent_count >= $limit;
    }

    public function getDailyRemainingAttribute(): int
    {
        $limit = $this->daily_limit;
        if ($limit <= 0) {
            return -1; // Unlimited
        }
        return max(0, $limit - $this->today_sent_count);
    }

    public function getDelayAfterCountAttribute()
    {
        return (int) ($this->attributes['delay_after_count'] ?? 50);
    }

    public function getDelayAfterDurationAttribute()
    {
        return (int) ($this->attributes['delay_after_duration'] ?? 5);
    }

    public function getResetAfterCountAttribute()
    {
        return (int) ($this->attributes['reset_after_count'] ?? 100);
    }

    public function getBatchSentCountAttribute(): int
    {
        return (int) ($this->attributes['batch_sent_count'] ?? 0);
    }
}
