<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DeviceMessage extends Model
{
    protected $guarded = ['id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappAccount()
    {
        return $this->belongsTo(WhatsappAccount::class, 'whatsapp_account_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        if ($this->status == 'sent' || $this->status == 'delivered') {
            return '<span class="badge badge--success"><i class="fas fa-check-double me-1"></i>' . trans('Sent') . '</span>';
        } elseif ($this->status == 'pending') {
            return '<span class="badge badge--warning"><i class="fas fa-spinner fa-spin me-1"></i>' . trans('Pending') . '</span>';
        } else {
            return '<span class="badge badge--danger"><i class="fas fa-times-circle me-1"></i>' . trans('Failed') . '</span>';
        }
    }

    public function mediaBadge(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->media_type == 'image') {
                    return '<span class="badge badge--primary"><i class="las la-image me-1"></i>' . trans('Image') . '</span>';
                } elseif ($this->media_type == 'video') {
                    return '<span class="badge badge--info"><i class="las la-video me-1"></i>' . trans('Video') . '</span>';
                } elseif ($this->media_type == 'audio') {
                    return '<span class="badge badge--secondary"><i class="las la-music me-1"></i>' . trans('Audio') . '</span>';
                } elseif ($this->media_type == 'document') {
                    return '<span class="badge badge--dark"><i class="las la-file-alt me-1"></i>' . trans('Document') . '</span>';
                } else {
                    return '<span class="badge badge--light text-dark"><i class="las la-comment-alt me-1"></i>' . trans('Text') . '</span>';
                }
            }
        );
    }
}
