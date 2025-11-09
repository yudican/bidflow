<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListOrderGp extends Model
{
    use HasFactory;
    protected $table = 'list_order_gp';
    protected $fillable = [
        'create_date',
        'submit_by',
        'status',
        'tax_name',
        'tax_value',
        'vat_value',
        'total_success',
        'total_failed',
    ];

    protected $appends = ['submit_by_name', 'total_extended_price', 'total_freight_amount', 'total_tax_amount', 'total_trade_discount', 'total_miscellaneous'];

    /**
     * Get all of the orderDetails for the ListOrderGp
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderDetails()
    {
        return $this->hasMany(ListOrderGpDetail::class, 'list_order_gp_id');
    }
    /**
     * Get the userSubmit that owns the ListOrderGp
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userSubmit()
    {
        return $this->belongsTo(User::class, 'submit_by');
    }

    public function getSubmitByNameAttribute()
    {
        $user = User::find($this->submit_by);
        if ($user) {
            return $user->name;
        }
        return '-';
    }

    public function getTotalExtendedPriceAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('list_order_gp_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->extended_price;
        }
        return $total;
    }

    public function getTotalFreightAmountAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('list_order_gp_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->freight_amount;
        }
        return $total;
    }

    public function getTotalTaxAmountAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('list_order_gp_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->tax_amount;
        }
        return $total;
    }

    public function getTotalTradeDiscountAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('list_order_gp_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->total_discount;
        }
        return $total;
    }

    public function getTotalMiscellaneousAttribute()
    {
        $total = 0;
        $orderDetails = ListOrderGpDetail::where('list_order_gp_id', $this->id)->get();
        foreach ($orderDetails as $orderDetail) {
            $total += $orderDetail->miscellaneous;
        }
        return $total;
    }
}
