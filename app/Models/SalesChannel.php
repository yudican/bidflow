<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_uid',
        'channel_name',
        'channel_description',
        'warehouse_id'
    ];

    protected $appends = ['warehouse_name'];

    /**
     * The productVariants that belong to the SalesChannel
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_channel', 'channel_uid', 'product_variant_id');
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
