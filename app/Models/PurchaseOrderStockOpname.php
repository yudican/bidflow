<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderStockOpname extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'stock_opname_date',
        'stock_opname_qty',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
