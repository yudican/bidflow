<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'product_name',
        'product_variant_name',
        'warehouse_name',
        'type_product',
        'type_stock',
        'type_transaction',
        'type_history',
        'name',
        'qty',
        'description',
    ];
}
