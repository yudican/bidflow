<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturItem extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['product_photo_url', 'u_of_m', 'sku'];

    /**
     * Get the product that owns the ReturItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id');
    }

    public function getProductPhotoUrlAttribute()
    {
        return $this->product_photo ? getImage($this->product_photo) : asset('assets/images/no-image.png');
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
