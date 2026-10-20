<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['name', 'type', 'media_url'];

    public function getNameAttribute()
    {
        return $this->attributes['title'] ?? ($this->attributes['name'] ?? '');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['title'] = $value;
    }

    public function getTypeAttribute()
    {
        return $this->attributes['category'] ?? ($this->attributes['type'] ?? 'text');
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['category'] = $value;
    }

    public function getContentAttribute()
    {
        return $this->attributes['message'] ?? ($this->attributes['content'] ?? '');
    }

    public function setContentAttribute($value)
    {
        $this->attributes['message'] = $value;
    }

    public function getMediaUrlAttribute()
    {
        return $this->attributes['media_url'] ?? '';
    }

    public function setMediaUrlAttribute($value)
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'media_url')) {
                $this->attributes['media_url'] = $value;
            }
        } catch (\Throwable $e) {
            // Silently fallback if schema check fails
        }
    }
}
