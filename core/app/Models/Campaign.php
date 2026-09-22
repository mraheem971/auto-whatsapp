<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'logs' => 'array',
    ];

    public function getMinDelaySecondsAttribute()
    {
        return $this->attributes['min_delay'] ?? ($this->attributes['delay_seconds'] ?? 5);
    }

    public function getMaxDelaySecondsAttribute()
    {
        return $this->attributes['max_delay'] ?? ($this->attributes['delay_seconds'] ? ($this->attributes['delay_seconds'] + 5) : 15);
    }
}
