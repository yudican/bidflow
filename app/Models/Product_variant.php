<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_variant extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['product_id','package_id','variant_id','detail_variant_id','name','slug','description','image','agent_price','customer_price','discount_price','discount_percent','stock','weight','status','sku','sku_variant'];
    
    protected $dates = [];
}
