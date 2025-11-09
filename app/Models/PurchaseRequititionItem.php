<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequititionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requitition_id',
        'item_name',
        'item_qty',
        'item_unit',
        'item_price',
        'item_tax',
        'item_url',
        'item_note',
    ];

    protected $appends = [
        'item_subtotal','item_id','item_sku'
    ];

    public function purchaseRequitition()
    {
        return $this->belongsTo(PurchaseRequitition::class);
    }

    public function getItemIdAttribute()
    {
        $product = ProductAdditional::where('name', $this->item_name)->first();
        return $product?->id;
    }

    public function getItemNameAttribute($value)
    {
        $product = ProductAdditional::find($value, ['name']);
        return $product?->name ?? $value;
    }

    public function getItemSubtotalAttribute()
    {
        if ($this->item_tax > 0) {
            $tax = $this->item_tax / 100;
            $subtotal = $this->item_qty * $this->item_price;
            $tax_subtotal = $subtotal * $tax;
            return $subtotal + $tax_subtotal;
        }
        return $this->item_qty * $this->item_price;
    }

    public function getItemSkuAttribute()
    {
        $product = ProductAdditional::find($this->item_id, ['sku']);
        return $product?->sku ?? '';
    }
}
