<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPOrderListItems extends Model
{
    use HasFactory;
    protected $table = 'mp_order_list_items';
    protected $fillable = [
        'mp_order_list_id',
        'sku',
        'product_name',
        'price',
        'final_price',
        'qty',
    ];

    protected $appends = ['trx_id'];

    public function getTrxIdAttribute()
    {
        $order = MPOrderList::find($this->mp_order_list_id,['trx_id']);
        if ($order) {
            return $order->trx_id;
        }

        return '-';
    }
}
