<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'user_id', 'qty', 'selected', 'product_variant_id'];
    protected $appends = ['subtotal', 'variants'];
    /**
     * Get the product that owns the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->product->price['final_price'] * $this->qty;
    }

    /**
     * Get the variant that owns the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getVariantsAttribute()
    {
        $productVarians = ProductVariant::where('product_id', $this->product_id)->where('sales_channel', 'like', '%agent-portal%')->get();

        return $productVarians;
    }
}
