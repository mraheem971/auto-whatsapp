<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    protected $guarded = ['id'];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'contact_list_id');
    }

    public function groupContacts()
    {
        return $this->hasMany(Contact::class, 'contact_list_id')->where('type', 'group');
    }

    public function directContacts()
    {
        return $this->hasMany(Contact::class, 'contact_list_id')->where('type', 'contact');
    }
}
