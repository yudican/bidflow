<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifAlert extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['name', 'uid', 'status'];

    protected $dates = [];
    public $table = "notif_alert";
    public $timestamps = false;

    /**
     * Get the product that owns the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    
}