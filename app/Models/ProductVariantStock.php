<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id',
        'qty',
        'stock_of_market',
        'warehouse_id',
        'company_id'
    ];

    protected $appends = ['warehouse_name'];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id);
        return $warehouse?->name ?? '-';
    }
}
