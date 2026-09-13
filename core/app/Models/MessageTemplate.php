<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $guarded = ['id'];

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
}
