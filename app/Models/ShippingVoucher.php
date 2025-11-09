<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistic_rate_id',
        'shipping_price_discount',
        'shipping_price_discount_start',
        'shipping_price_discount_end',
        'shipping_price_sales_channel',
        'shipping_price_discount_status',
    ];

    public function logisticRate()
    {
        return $this->belongsTo(LogisticRate::class);
    }
}
