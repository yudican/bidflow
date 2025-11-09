<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderListByGenie extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['total_freight_amount', 'total_trade_discount', 'total_miscellaneous'];

    /**
     * Get all of the listOrderGp for the OrderListByGenie
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function listOrderGp()
    {
        return $this->hasMany(ListOrderGpDetail::class, 'ginee_order_id');
    }

    public function getTotalFreightAmountAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('ginee_order_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->freight_amount;
        }
        return $total;
    }

    public function getTotalTradeDiscountAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('ginee_order_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->total_discount;
        }
        return $total;
    }

    public function getTotalMiscellaneousAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('ginee_order_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->miscellaneous;
        }
        return $total;
    }
}
