<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandCustomerSupport extends Model
{
    use HasFactory;
    protected $guarded = [];

    // belongsTo Brand (one to one) model
    function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
