<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetControlLog extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'user_name'
    ];

    public function getUserNameAttribute()
    {
        $user = User::find($this->executed_by, ['name']);
        return $user ? $user->name : '-';
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
