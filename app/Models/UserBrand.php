<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBrand extends Model
{
    //use Uuid;
    use HasFactory;

    protected $table = 'brand_user';

    //public $incrementing = false;

    protected $fillable = ['brand_id', 'user_id'];

    protected $dates = [];
}
