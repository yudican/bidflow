<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid_inventory',
        'product_id',
        'price',
        'qty',
        'subtotal',
        'type',
        'ref',
        'notes',
        'case_return',
        'qty_diterima',
        'is_master',
        'received_number',
        'received_vendor',
        'created_by'
    ];

    protected $appends = ['sku', 'u_of_m', 'product_name', 'created_by_name'];

    /**
     * Get the product that owns the InventoryItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id');
    }

    public function productMaster()
    {
        return $this->belongsTo(Product::class, 'product_id');
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

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by, ['name']);
        return $user ? $user->name : '-';
    }


    /**
     * Get the stock that owns the InventoryItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventoryReturn()
    {
        return $this->belongsTo(InventoryProductReturn::class, 'uid_inventory', 'uid_inventory');
    }

    public function getSkuAttribute()
    {
        if ($this->inventoryReturn) {
            if ($this->inventoryReturn->type_return == 'po') {
                $product = Product::find($this->product_id);
                return $product->sku ?? '-';
            }
        }
        $product = ProductVariant::find($this->product_id);
        return $product->sku ?? '-';
    }

    public function getUOfMAttribute()
    {
        $sku_master = SkuMaster::where('sku', $this->sku)->where('status', 1)->first();
        return $sku_master?->package_name ?? '-';
    }

    public function getProductNameAttribute()
    {
        if ($this->inventoryReturn) {
            if ($this->inventoryReturn->type_return == 'po') {
                $product = Product::find($this->product_id);
                return $product->name ?? '-';
            }
        }
        $product = ProductVariant::find($this->product_id);
        return $product?->name ?? '-';
    }
}
