<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderSubmitLogDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_submit_log_id',
        'order_id',
        'status',
        'error_message',
    ];

    protected $appends = [
        'order',
        'extended_price',
        'discount_amount',
        'tax_amount',
        'misc_amount',
        'freight',
    ];

    public function orderSubmitLog()
    {
        return $this->belongsTo(OrderSubmitLog::class);
    }


    public function getOrderAttribute()
    {
        $order = OrderSubmitLog::find($this->order_submit_log_id);
        $type_si =  $order ? $order->type_si : 0;

        if ($type_si == 'order-lead') {
            return OrderLead::find($this->order_id);
        }

        if ($type_si == 'purchase-order') {
            return PurchaseOrder::find($this->order_id);
        }
        if ($type_si == 'receiving-purchase-order') {
            return PurchaseOrderItem::find($this->order_id);
        }
        if ($type_si == 'purchasing-invoice-entry') {
            return PurchaseInvoiceEntry::find($this->order_id);
        }
        if ($type_si == 'manual-payment-entry') {
            return PurchaseInvoiceEntry::find($this->order_id);
        }
        if ($type_si == 'payables-entry') {
            return PurchaseInvoiceEntry::find($this->order_id);
        }
        if ($type_si == 'customer-contact') {
            return User::find($this->order_id);
        }
        if ($type_si == 'vendor') {
            return Vendor::find($this->order_id);
        }
        if ($type_si == 'invoice-so') {
            return LeadBilling::find($this->order_id);
        }
        if ($type_si == 'marketplace') {
            return MPOrderList::find($this->order_id);
        }

        if ($type_si == 'submit-ethix') {
            return MPOrderListItems::find($this->order_id);
        }
        if ($type_si == 'telmark') {
            $order = DB::table('transactions')->where('id',$this->order_id)->select('id_transaksi')->first();
            if ($order) {
                return ['trx_id' => $order->id_transaksi];
            }
        }
        // if ($type_si == 'inventory-transfer') {
        //     return MPOrderList::find($this->order_id);
        // }
        return OrderManual::find($this->order_id);
    }

    public function getTaxAmountAttribute()
    {
        $order = OrderSubmitLog::find($this->order_submit_log_id);
        $tax =  $order ? $order->tax : 0;
        $type_si =  $order ? $order->type_si : 0;

        if (in_array($type_si, ['order-lead', 'order-manual', 'freebies'])) {
            if ($this->order) {
                if ($tax > 0) {
                    $total = 0;
                    $tax_amount = $tax / 100;
                    foreach ($this->order->productNeeds as $key => $product) {
                        $total += $product->price_product * $product->qty;
                    }
                    return $total * $tax_amount;
                }

                // $total = 0;
                // foreach ($this->order->productNeeds as $key => $product) {
                //     $total += $product->price_product * $product->qty;
                // }

                // return $total;
            }
        }
        if (in_array($type_si, ['marketplace'])) {
            if ($this->order) {
                $total = 0;
                $tax_amount = 0.11;
                foreach ($this->order->items as $key => $product) {
                    $total += $product->final_price * $product->qty;
                }
                return $total * $tax_amount;

                // $total = 0;
                // foreach ($this->order->items as $key => $product) {
                //     $total += $product->final_price * $product->qty;
                // }

                // return $total;
            }
        }
        if (in_array($type_si, ['purchase-order'])) {
            if ($this->order) {
                if ($tax > 0) {
                    $total = 0;
                    $tax_amount = $tax / 100;
                    foreach ($this->order->items as $key => $product) {
                        $total += $product->price * $product->qty;
                    }
                    return $total * $tax_amount;

                    // $total = 0;
                    // foreach ($this->order->items as $key => $product) {
                    //     $total += $product->price * $product->qty;
                    // }

                    // return $total;
                }
            }
        }
        if (in_array($type_si, ['purchasing-invoice-entry', 'payables-entry'])) {
            if ($this->order) {
                $tax = MasterTax::find($order->tax);
                $total = 0;
                $tax_amount = $tax->tax_percentage / 100;
                foreach ($this->order->items as $key => $product) {
                    $total += $product->extended_cost;
                }

                return $total * $tax_amount;
            }
        }


        return 0;
    }

    public function getExtendedPriceAttribute()
    {
        $order = OrderSubmitLog::find($this->order_submit_log_id);
        $type_si =  $order ? $order->type_si : 0;

        $total = 0;
        if ($this->order) {
            if (in_array($type_si, ['order-lead', 'order-manual', 'freebies'])) {
                $productNeeds = ProductNeed::where('uid_lead', $this->order->uid_lead)->get();
                foreach ($productNeeds as $key => $product) {
                    $total += $product->price_product * $product->qty;
                }
                $vat =  $this->orderSubmitLog->vat > 0 ? $this->orderSubmitLog->vat : 1;
                if ($vat > 0) {
                    if ($total > 0) {
                        return $total / $vat;
                    }

                    return $total;
                }

                return $total;
            }

            if (in_array($type_si, ['marketplace'])) {
                $productNeeds = MPOrderListItems::where('mp_order_list_id', $this->order->id)->get();
                foreach ($productNeeds as $key => $product) {
                    $total += $product->final_price * $product->qty;
                }

                return $total + $this->tax_amount;
            }

            if (in_array($type_si, ['purchase-order'])) {
                $productNeeds = PurchaseOrderItem::where('purchase_order_id', $this->order->id)->get();
                foreach ($productNeeds as $key => $product) {
                    $total += $product->price * $product->qty;
                }
                $vat =  $this->orderSubmitLog->vat > 0 ? $this->orderSubmitLog->vat : 1;
                if ($vat > 0) {
                    if ($total > 0) {
                        return $total / $vat;
                    }

                    return $total;
                }
                return $total;
            }

            if (in_array($type_si, ['purchasing-invoice-entry', 'payables-entry'])) {
                $productNeeds = PurchaseInvoiceEntryItem::where('purchase_invoice_entry_id', $this->order->id)->get();
                foreach ($productNeeds as $key => $product) {
                    $total += $product->extended_cost;
                }

                $vat =  $this->orderSubmitLog->vat > 0 ? $this->orderSubmitLog->vat : 1;
                if ($vat > 0) {
                    if ($total > 0) {
                        return $total / $vat;
                    }

                    return $total;
                }
                return $total;
            }
            if ($type_si == 'telmark') {
                $order = DB::table('transactions')->where('id',$this->order_id)->select('nominal')->first();
                if ($order) {
                    return $order->nominal;
                }
            }
        }

        return 0;
    }

    public function getDiscountAmountAttribute()
    {
        $order = OrderSubmitLog::find($this->order_submit_log_id);
        $type_si =  $order ? $order->type_si : 0;
        if (in_array($type_si, ['marketplace'])) {
            $productNeeds = MPOrderList::where('mp_order_list_id', $this->order->id)->first();
            $shiping_fee = $productNeeds->shipping_fee ?? 0;
            $shipping_fee_deference = $productNeeds->shipping_fee_deference ?? 0;
            $platform_rebate = $productNeeds->platform_rebate ?? 0;
            $voucher_seller = $productNeeds->voucher_seller ?? 0;
            $platform_fulfilment = $productNeeds->platform_fulfilment ?? 0;
            $service_fee = $productNeeds->service_fee ?? 0;

            return $shiping_fee + $shipping_fee_deference + $platform_rebate - $voucher_seller - $platform_fulfilment - $service_fee;
        }

        if ($type_si == 'telmark') {
            $order = DB::table('transactions')->where('id',$this->order_id)->select('diskon')->first();
            if ($order) {
                return $order->diskon;
            }
        }

        if ($this->order) {
            return $this->order?->discount_amount ?? 0;
        }
        return 0;
    }



    public function getMiscAmountAttribute()
    {
        return 0;
    }

    public function getFreightAttribute()
    {
        $order = OrderSubmitLog::find($this->order_submit_log_id);
        $type_si =  $order ? $order->type_si : 0;
        if ($type_si == 'telmark') {
            $order = DB::table('transactions')->where('id',$this->order_id)->select('deduction')->first();
            if ($order) {
                return $order->deduction;
            }
        }
        if ($this->order) {
            return $this->order?->ongkir ?? $this->order?->shipping_fee ?? 0;
        }

        return 0;
    }
}
