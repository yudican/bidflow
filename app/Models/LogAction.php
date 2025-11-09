<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'model_id',
        'action',
        'description',
    ];

    protected $appends = ['user_name'];


    /**
     * Get the user that owns the LogAction
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getUserNameAttribute()
    {
        return $this->user?->name ?? '-';
    }
}
