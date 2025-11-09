<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCase extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['type_name','code','notes'];
    
    protected $dates = [];
}
