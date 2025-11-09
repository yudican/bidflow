<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTax extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;
    protected $table = "master_tax";

    protected $fillable = ['tax_code', 'tax_percentage', 'gp_status'];

    protected $dates = [];

    
}
