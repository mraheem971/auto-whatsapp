<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isValid()
    {
        if (!$this->status) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }
}
