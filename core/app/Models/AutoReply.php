<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    use HasFactory;

    protected $table = 'auto_replies';

    protected $guarded = ['id'];

    protected $casts = [
        'status'                  => 'boolean',
        'hit_count'               => 'integer',
        'read_delay_seconds'      => 'integer',
        'typing_duration_seconds' => 'integer',
        'reply_delay_seconds'     => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(WhatsappAccount::class, 'session_id', 'session_id');
    }

    public function contactList()
    {
        return $this->belongsTo(ContactList::class, 'contact_list_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
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

    public function scopeForSession($query, $sessionId)
    {
        return $query->where(function ($q) use ($sessionId) {
            $account = !empty($sessionId) ? WhatsappAccount::where('session_id', $sessionId)->first() : null;

            if ($account && $account->user_id > 0) {
                // User account: ONLY match this specific user's rules
                $q->where('user_id', $account->user_id)
                  ->where(function ($sq) use ($sessionId) {
                      $sq->whereNull('session_id')
                         ->orWhere('session_id', '')
                         ->orWhere('session_id', $sessionId);
                  });
            } else {
                // Admin account: ONLY match admin rules (user_id is null or 0)
                $q->where(function ($adminQ) {
                    $adminQ->whereNull('user_id')->orWhere('user_id', 0);
                })->where(function ($sq) use ($sessionId) {
                    $sq->whereNull('session_id')
                       ->orWhere('session_id', '')
                       ->orWhere('session_id', $sessionId);
                });
            }
        });
    }

    /**
     * Get keywords as an array
     */
    public function getKeywordsArrayAttribute()
    {
        if (empty($this->keywords)) {
            return [];
        }
        $decoded = json_decode($this->keywords, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->keywords))));
    }

    /**
     * Get target contacts as an array
     */
    public function getTargetContactsArrayAttribute()
    {
        if (empty($this->target_contacts)) {
            return [];
        }
        $decoded = json_decode($this->target_contacts, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->target_contacts))));
    }

    /**
     * Get target group ids as an array
     */
    public function getTargetGroupIdsArrayAttribute()
    {
        if (empty($this->target_group_ids)) {
            return [];
        }
        $decoded = json_decode($this->target_group_ids, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_values(array_filter(array_map('trim', explode(',', $this->target_group_ids))));
    }
}
