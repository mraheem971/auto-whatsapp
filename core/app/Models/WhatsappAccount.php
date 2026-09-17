<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class WhatsappAccount extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'connection_data' => 'array',
        'last_connected_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        if ($this->status == 1) {
            return '<span class="badge badge--success"><i class="fas fa-check-circle me-1"></i>' . trans('Connected') . '</span>';
        } else {
            return '<span class="badge badge--warning"><i class="fas fa-spinner fa-spin me-1"></i>' . trans('Pending / Disconnected') . '</span>';
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    public function scopeAdminOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('user_id')->orWhere('user_id', 0);
        });
    }

    public function scopeUserOnly($query)
    {
        return $query->whereNotNull('user_id')->where('user_id', '>', 0);
    }
}
