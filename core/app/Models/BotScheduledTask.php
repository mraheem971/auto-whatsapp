<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BotScheduledTask extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'next_run_at'       => 'datetime',
        'last_run_at'       => 'datetime',
        'schedule_datetime' => 'datetime',
    ];

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        if ($this->status == 1) {
            return '<span class="badge badge--success"><i class="fas fa-play-circle me-1"></i>' . trans('Active') . '</span>';
        } elseif ($this->status == 2) {
            return '<span class="badge badge--dark"><i class="fas fa-check-circle me-1"></i>' . trans('Completed') . '</span>';
        } else {
            return '<span class="badge badge--warning"><i class="fas fa-pause-circle me-1"></i>' . trans('Paused') . '</span>';
        }
    }

    public function eventTypeBadge(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->event_type == 'reminder') {
                    return '<span class="badge badge--warning text-dark"><i class="las la-bell me-1"></i>' . trans('Reminder') . '</span>';
                } elseif ($this->event_type == 'update') {
                    return '<span class="badge badge--info"><i class="las la-bullhorn me-1"></i>' . trans('Update') . '</span>';
                } elseif ($this->event_type == 'broadcast') {
                    return '<span class="badge badge--primary"><i class="las la-broadcast-tower me-1"></i>' . trans('Broadcast') . '</span>';
                } else {
                    return '<span class="badge badge--secondary"><i class="las la-calendar-check me-1"></i>' . trans('Custom') . '</span>';
                }
            }
        );
    }

    /**
     * Compute next_run_at timestamp based on schedule type
     */
    public function computeNextRunAt()
    {
        $now = Carbon::now();

        if ($this->schedule_type === 'once') {
            return $this->schedule_datetime;
        } elseif ($this->schedule_type === 'daily') {
            $time = $this->schedule_time ?: '09:00:00';
            $next = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d') . ' ' . $time);
            if ($next->isPast()) {
                $next->addDay();
            }
            return $next;
        } elseif ($this->schedule_type === 'weekly') {
            $time = $this->schedule_time ?: '09:00:00';
            $dayOfWeek = $this->schedule_day_of_week ?? 1; // Default Monday
            $next = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d') . ' ' . $time);
            
            // Adjust day of week
            while ($next->dayOfWeek !== $dayOfWeek || $next->isPast()) {
                $next->addDay();
            }
            return $next;
        } elseif ($this->schedule_type === 'monthly') {
            $time = $this->schedule_time ?: '09:00:00';
            $dayOfMonth = min(max((int) ($this->schedule_day_of_month ?? 1), 1), 28);
            $next = Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-') . sprintf('%02d', $dayOfMonth) . ' ' . $time);
            if ($next->isPast()) {
                $next->addMonth();
            }
            return $next;
        }

        return $now->addDay();
    }
}
