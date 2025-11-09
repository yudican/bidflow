<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactGroupMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'contact_id',
        'is_admin',
        'contact_group_id',
    ];
}
