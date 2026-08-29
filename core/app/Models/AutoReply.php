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
        'match_type',
        'keywords',
        'reply_message',
        'status',
        'hit_count',
        'cooldown_seconds',
    ];

    protected $casts = [
        'status'           => 'boolean',
        'hit_count'        => 'integer',
        'cooldown_seconds' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(WhatsappAccount::class, 'session_id', 'session_id');
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
}
