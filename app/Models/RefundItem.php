<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['u_of_m', 'sku'];

    /**
     * Get the product that owns the RefundItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id');
    }

    public function getUOfMAttribute()
    {
        $product = ProductVariant::find($this->product_id);

        return $product ? $product->u_of_m : '-';
    }

    public function getSkuAttribute()
    {
        $product = ProductVariant::find($this->product_id);

        return $product ? $product->sku : '-';
    }
}
