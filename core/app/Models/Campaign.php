<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'logs'         => 'array',
        'next_send_at' => 'datetime',
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
}
