<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListOrderGpDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'list_order_gp_id',
        'ginee_order_id',
        'so_number',
        'batch_number',
        'status',
    ];

    protected $appends = ['u_of_m', 'freight_amount', 'tax_amount', 'subtotal_amount', 'total_discount', 'miscellaneous', 'extended_price', 'tax_value', 'cek'];

    /**
     * Get the listOrderGp that owns the ListOrderGpDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function listOrderGp()
    {
        return $this->belongsTo(ListOrderGp::class, 'list_order_gp_id');
    }

    /**
     * Get the ginee that owns the ListOrderGpDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderGinee()
    {
        return $this->belongsTo(OrderListByGenie::class, 'ginee_order_id');
    }

    public function getTaxValueAttribute()
    {
        return $this->listOrderGp?->tax_value ? $this->listOrderGp->tax_value / 100 : 0.11;
    }

    public function getUOfMAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            $sku = SkuMaster::where('sku', $orderGinee->sku)->where('status', 1)->first();
            if ($sku) {
                return $sku->package ? $sku->package->name : '-';
            }
            return '-';
        }

        return '-';
    }

    public function getExtendedPriceAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            $subtotal = $orderGinee->harga_promo * $orderGinee->qty;
            return round($subtotal / $this->listOrderGp->vat_value);
        }
        return 0;
    }

    public function getFreightAmountAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            if ($orderGinee->subsidi_angkutan > 0) {
                return $orderGinee->ongkir_dibayar_sistem - $orderGinee->total_diskon + $orderGinee->subsidi_angkutan;
            }
            if ($orderGinee->ongkir < 1) {
                $subtotal = $orderGinee->harga_promo * $orderGinee->qty;
                $biaya_komisi = $orderGinee->biaya_komisi ?? 0;
                $biaya_layanan = $orderGinee->biaya_layanan ?? 0;
                return $subtotal - ($biaya_komisi + $biaya_layanan);
            }
        }

        return 0;
    }

    public function getTaxAmountAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            $total_tax = $this->extended_price * $this->tax_value;
            return $total_tax;
        }
    }

    public function getSubtotalAmountAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            $nominal = $orderGinee->harga_promo * $orderGinee->qty ?? 0;

            if ($this->tax_value > 0) {
                return $nominal * $this->tax_value;
            }
            return $nominal;
        }
        return 0;
    }

    public function getTotalDiscountAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            $total_diskon = $orderGinee->total_diskon ?? 0;
            return $total_diskon;
        }
        return 0;
    }

    public function getMiscellaneousAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            return $orderGinee->biaya_komisi + $orderGinee->biaya_layanan;
        }
        return 0;
    }

    public function getCekAttribute()
    {
        $orderGinee = OrderListByGenie::find($this->ginee_order_id);
        if ($orderGinee) {
            $subtotal = $orderGinee->harga_promo * $orderGinee->qty;
            $service = $orderGinee->biaya_komisi + $orderGinee->biaya_layanan;
            $ongkir_dibayar_sistem = $orderGinee->ongkir_dibayar_sistem;

            $total_diskon = $orderGinee->total_diskon + $orderGinee->subsidi_angkutan;

            $total = $subtotal - $service + $ongkir_dibayar_sistem - $total_diskon;

            return $total;
        }
        return 0;
    }
}
