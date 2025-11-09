<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAllocationHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid_inventory',
        'product_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'quantity',
        'sku',
        'u_of_m',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function inventory()
    {
        return $this->belongsTo(InventoryProductStock::class, 'uid_inventory', 'uid_inventory');
    }

    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id')->select(['id', 'name']);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id')->select(['id', 'name']);
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id')->select(['id', 'name']);
    }
}
