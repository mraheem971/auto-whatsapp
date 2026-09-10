<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BotNotificationLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

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

    public function eventTypeBadge(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->event_type == 'system_error') {
                    return '<span class="badge badge--danger"><i class="las la-exclamation-triangle me-1"></i>' . trans('System Error') . '</span>';
                } elseif ($this->event_type == 'user_error_escalation') {
                    return '<span class="badge badge--warning text-dark"><i class="las la-user-shield me-1"></i>' . trans('User Error Escalation') . '</span>';
                } elseif ($this->event_type == 'reminder') {
                    return '<span class="badge badge--info"><i class="las la-bell me-1"></i>' . trans('Reminder') . '</span>';
                } elseif ($this->event_type == 'scheduled_task') {
                    return '<span class="badge badge--primary"><i class="las la-calendar-check me-1"></i>' . trans('Scheduled Task') . '</span>';
                } else {
                    return '<span class="badge badge--secondary"><i class="las la-comment-dots me-1"></i>' . trans('New Message Alert') . '</span>';
                }
            }
        );
    }
}
