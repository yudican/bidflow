<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseLogApproval extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'user_name'
    ];

    public function getUserNameAttribute()
    {
        $user = User::find($this->execute_by, ['name']);
        return $user ? $user->name : '-';
    }
}
