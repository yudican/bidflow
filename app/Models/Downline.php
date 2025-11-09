<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Downline extends Model
{
    use HasFactory;
    protected $guarded = [];


    /**
     * Get the user that owns the Downline
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the user that owns the Downline
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
