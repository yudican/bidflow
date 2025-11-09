<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPOrderList extends Model
{
    use HasFactory;
    protected $table = 'mp_order_lists';
    protected $fillable = [
        'trx_id',
        'customer_code',
        'customer_name',
        'channel',
        'store',
        'amount',
        'shipping_fee',
        'payment_method',
        'warehouse',
        'mp_fee',
        'discount',
        'trx_date',
        'courir',
        'awb',
        'status',
        'shipping_status',
        'gp_number',
        'status_ethix',
        'status_gp',
        'shipping_fee_non_cashlesh',
        'platform_rebate',
        'voucher_seller',
        'shipping_fee_deference',
        'platform_fulfilment',
        'service_fee',
    ];

    protected $appends = ['warehouse_ethix', 'total_amount', 'balance_due', 'vat_in', 'vat_out'];

    /**
     * Get all of the items for the MPOrderList
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(MPOrderListItems::class, 'mp_order_list_id');
    }

    public function getWarehouseEthixAttribute()
    {
        $warehouse = Warehouse::where('wh_id', $this->warehouse)->first(['ethix_id']);
        return $warehouse ? $warehouse->ethix_id : '-';
    }

    public function getTotalAmountAttribute()
    {
        $priceTotal = 0;
        foreach ($this->items as $key => $item) {
            $priceTotal += $item->price;
        }
        $shiping_fee = $this->shipping_fee ?? 0;
        $shipping_fee_deference = $this->shipping_fee_deference ?? 0;
        $platform_rebate = $this->platform_rebate ?? 0;
        $voucher_seller = $this->voucher_seller ?? 0;
        $platform_fulfilment = $this->platform_fulfilment ?? 0;
        $service_fee = $this->service_fee ?? 0;


        return $priceTotal + $shiping_fee + $shipping_fee_deference + $platform_rebate - $voucher_seller - $platform_fulfilment - $service_fee - $shipping_fee_deference;
    }

    public function getBalanceDueAttribute()
    {
        return $this->total_amount;
    }

    public function getVatInAttribute()
    {
        return $this->service_fee * 0.11;
    }

    public function getVatOutAttribute()
    {
        $priceTotal = 0;
        foreach ($this->items as $key => $item) {
            $priceTotal += $item->price;
        }
        return ($priceTotal / 1.11) * 0.11;
    }
}
