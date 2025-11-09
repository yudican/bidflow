<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid_retur', 'product_id', 'price', 'qty', 'status', 'tax_id', 'discount_id'
    ];

    protected $appends = ['tax_amount', 'discount_amount', 'subtotal', 'total', 'role', 'price_product'];

    /**
     * Get the salesReturMaster that owns the SalesReturnItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesReturMaster()
    {
        return $this->belongsTo(SalesReturn::class, 'uid_retur', 'uid_retur');
    }

    /**
     * Get the product that owns the ProductNeed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id');
    }

    public function discount()
    {
        return $this->belongsTo(MasterDiscount::class);
    }

    public function tax()
    {
        return $this->belongsTo(MasterTax::class);
    }

    public function getRoleAttribute()
    {
        $row = SalesReturn::where('uid_retur', $this->uid_retur)->first();
        if ($row) {
            if ($row->contact) {
                $user = User::find($row->contact);
                if ($user && $user->role) {
                    return $user->role->role_type;
                }
            }
        }

        return 'agent';
    }

    public function getPriceProductAttribute()
    {
        if ($this->product) {
            $price = $this->product->getPrice($this->role)['final_price'];
            if ($this->price > 0) {
                $price = $this->price;
            }
            return $price;
        }
        return 0;
    }

    public function getTaxAmountAttribute()
    {
        if ($this->tax) {
            return $this->price_product * $this->tax->tax_percentage / 100;
        }

        return 0;
    }

    public function getDiscountAmountAttribute()
    {
        if ($this->discount) {
            return $this->price_product * $this->discount->percentage / 100;
        }

        return 0;
    }

    public function getTotalAttribute()
    {
        $qty = $this->price > 0 ? 1 : $this->qty;
        return $this->price_product * $qty + $this->tax_amount - $this->discount_amount;
    }



    public function getSubtotalAttribute()
    {
        $qty = $this->price > 0 ? 1 : $this->qty;
        return $this->price_product * $qty;
    }
}
