<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturResi extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['created_name'];

    public function getCreatedNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user ? $user->name : '-';
    }
}
