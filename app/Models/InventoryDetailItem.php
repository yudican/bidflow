<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDetailItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid_inventory',
        'product_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'master_bin_id',
        'sku',
        'u_of_m',
        'qty',
        'qty_alocation',
        'notes',
        'case_return',
        'tax_id',
        'tax_amount',
        'tax_percentage',
        'discount_percentage',
        'discount',
        'discount_amount',
        'subtotal',
        'price_nego',
        'total',
        'stock_awal'
    ];

    protected $appends = ['product_name', 'product_price'];

    /**
     * Get the warehouse that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }
    /**
     * Get the warehouse that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseDestination()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the stock that owns the InventoryItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventoryStock()
    {
        return $this->belongsTo(InventoryProductStock::class, 'uid_inventory', 'uid_inventory');
    }

    public function orderTransfer()
    {
        return $this->belongsTo(OrderTransfer::class, 'uid_inventory', 'uid_inventory');
    }

    public function getProductNameAttribute()
    {
        $product = Product::find($this->product_id, ['name']);
        return $product?->name ?? '-';
    }

    public function getProductPriceAttribute()
    {
        $product = Product::find($this->product_id, ['id']);
        if ($product?->price) {
            return $product?->price['final_price'] ?? 0;
        }

        return 0;
    }
}
