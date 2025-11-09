<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantBundlingStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_bundling_id',
        'qty',
        'stock_off_market',
        'warehouse_id',
        'company_id',
        'description',
    ];

    protected $appends = ['warehouse_name'];

    /**
     * Get the bundling that owns the ProductVariantBundlingStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bundling()
    {
        return $this->belongsTo(ProductVariantBundling::class, 'product_variant_bundling_id');
    }

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id);
        return $warehouse?->name ?? '-';
    }
}
