<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantBundling extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'product_qty',
        'package_id',
        'company_id',
        'sku',
        'is_master'
    ];


    protected $appends = ['product_name', 'product_variant_name', 'uom'];

    /**
     * Get the product that owns the ProductVariantBundling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all of the productStock for the ProductVariantBundling
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productStock()
    {
        return $this->hasMany(ProductStock::class, 'product_id', 'product_id')->orderBy('stock', 'asc');
    }

    /**
     * Get the productVariant that owns the ProductVariantBundling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the package that owns the ProductVariantBundling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function getProductNameAttribute()
    {
        return $this->product?->name ?? '-';
    }

    public function getProductVariantNameAttribute()
    {
        return $this->productVariant?->name ?? '-';
    }

    public function getUomAttribute()
    {
        return $this->package?->name ?? '-';
    }
}
