<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    use HasFactory;

    protected $table = 'auto_replies';

    protected $fillable = [
        'admin_id',
        'session_id',
        'name',
        'chat_scope',
        'target_type',
        'target_contacts',
        'target_group_ids',
        'contact_list_id',
        'match_type',
        'keywords',
        'reply_message',
        'reply_destination',
        'read_delay_seconds',
        'typing_duration_seconds',
        'reply_delay_seconds',
        'status',
        'hit_count',
    ];

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

    public function scopeForSession($query, $sessionId)
    {
        return $query->where(function ($q) use ($sessionId) {
            $q->whereNull('session_id')
              ->orWhere('session_id', '')
              ->orWhere('session_id', $sessionId);

            if (!empty($sessionId)) {
                $account = WhatsappAccount::where('session_id', $sessionId)->first();
                if ($account && $account->phone_number) {
                    $otherSessionIds = WhatsappAccount::where('phone_number', $account->phone_number)
                        ->pluck('session_id')
                        ->filter()
                        ->toArray();
                    if (!empty($otherSessionIds)) {
                        $q->orWhereIn('session_id', $otherSessionIds);
                    }
                }
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
