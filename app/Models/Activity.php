<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'attachment',
        'created_by',
    ];

    protected $appends = ['created_by_name'];

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by, ['name']);

        if ($user) {
            return $user->name;
        }

        return '-';
    }
}
