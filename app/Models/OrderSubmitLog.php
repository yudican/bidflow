<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderSubmitLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'submited_by',
        'updated_by',
        'type_si',
        'vat',
        'tax',
        'ref_id',
        'ref_number',
        'child_id',
        'company_id',
        'body'
    ];

    protected $appends = [
        'submited_by_name',
        'updated_by_name',
        'success',
        'failed',
        'message',
        'po_number',
        'gp_po_number',
        'created_at_formatted'
    ];

    /**
     * Get all of the submitedBy for the OrderSubmitLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submitedBy()
    {
        return $this->belongsTo(User::class, 'submited_by');
    }

    public function orderSubmitLogDetails()
    {
        return $this->hasMany(OrderSubmitLogDetail::class);
    }


    public function getSubmitedByNameAttribute()
    {
        return $this->submited_by ? User::find($this->submited_by, ['name'])->name : '-';
    }

    public function getUpdatedByNameAttribute()
    {
        return $this->updated_by ? User::find($this->updated_by, ['name'])->name : '-';
    }

    public function getSuccessAttribute()
    {
        return $this->orderSubmitLogDetails()->where('status', 'success')->count();
    }

    public function getFailedAttribute()
    {
        return $this->orderSubmitLogDetails()->where('status', 'failed')->count();
    }

    public function getMessageAttribute()
    {
        return $this->orderSubmitLogDetails()->first(['error_message'])?->error_message ?? '-';
    }

    function getPoNumberAttribute()
    {
        if ($this->type_si == 'purchase-order') {
            $po = PurchaseOrder::find($this->ref_id, ['po_number']);
            if ($po) {
                return $po->po_number;
            }
        }
        if ($this->type_si == 'receiving-purchase-order') {


            $po = PurchaseOrderItem::where('purchase_order_id', $this->ref_id)->first();
            if ($po) {
                return $po->purchaseOrder?->po_number ?? '-';
            }
        }
        if ($this->type_si == 'marketplace') {
            $po = MPOrderList::find($this->ref_id);
            if ($po) {
                return $po->trx_id ?? '-';
            }
        }

        if ($this->type_si == 'purchasing-invoice-entry') {
            $po = PurchaseInvoiceEntry::find($this->ref_id, ['received_number']);
            if ($po) {
                return $po->received_number ?? '-';
            }
        }

        if ($this->type_si == 'payables-entry') {
            $po = PurchaseInvoiceEntry::find($this->ref_id, ['received_number']);
            if ($po) {
                return $po->received_number ?? '-';
            }
        }

        if ($this->type_si == 'manual-payment-entry') {
            $po = PurchaseBilling::find($this->ref_id, ['payment_number']);
            if ($po) {
                return $po->payment_number ?? '-';
            }
        }

        if ($this->type_si == 'customer-contact') {
            $po = User::whereUid($this->ref_id)->first(['uid']);
            if ($po) {
                return $po->uid ?? '-';
            }
        }

        if ($this->type_si == 'vendor') {
            $po = Vendor::find($this->ref_id, ['id']);
            if ($po) {
                return $po->id ?? '-';
            }
        }

        if (in_array($this->type_si, ['order-manual', 'transaction-agent'])) {
            $order = OrderManual::find($this->ref_id, ['invoice_number']);
            if ($order) {
                return $order->invoice_number ?? '-';
            }
        }

        if ($this->type_si == 'submit-ethix') {
            $order = MPOrderList::find($this->ref_id, ['trx_id']);
            if ($order) {
                return $order->trx_id ?? '-';
            }
        }

        if ($this->type_si == 'invoice') {
            $orderManual = OrderManual::where('id', $this->ref_id)->first(['invoice_number']);
            if ($orderManual) {
                if ($orderManual) {
                    return $orderManual->invoice_number ?? '-';
                }
            }
            $orderLead = OrderManual::where('id', $this->ref_id)->first(['invoice_number']);
            if ($orderLead) {
                return $orderManual->invoice_number ?? '-';
            }
        }

        if (in_array($this->type_si, ['telmark', 'trx_general'])) {
            $order = DB::table('transactions')->where('id', $this->ref_id)->select('invoice_number')->first();
            if ($order) {
                return $order->invoice_number ?? '-';
            }
        }


        return '-';
    }


    function getGpPoNumberAttribute()
    {
        if ($this->type_si == 'purchase-order') {
            $po = PurchaseOrder::find($this->ref_id, ['gp_po_number']);
            if ($po) {
                return $po->gp_po_number;
            }
        }

        if ($this->type_si == 'receiving-purchase-order') {
            if ($this->child_id) {
                $po = PurchaseOrderItem::where('purchase_order_id', $this->ref_id)->where('id', $this->child_id)->first(['gp_received_number']);
                if ($po) {
                    return $po->gp_received_number;
                }
            }

            $po = PurchaseOrderItem::where('purchase_order_id', $this->ref_id)->first(['gp_received_number']);
            if ($po) {
                return $po->gp_received_number;
            }
        }

        if ($this->type_si == 'inventory-transfer') {
            $po = InventoryProductStock::find($this->ref_id, ['gp_transfer_number']);
            if ($po) {
                return $po->gp_transfer_number ?? '-';
            }
        }

        if ($this->type_si == 'purchasing-invoice-entry') {
            $po = PurchaseInvoiceEntry::find($this->ref_id, ['gp_invoice_number']);
            if ($po) {
                return $po->gp_invoice_number ?? '-';
            }
        }

        if ($this->type_si == 'payables-entry') {
            $po = PurchaseInvoiceEntry::find($this->ref_id, ['gp_invoice_number']);
            if ($po) {
                return $po->gp_invoice_number ?? '-';
            }
        }

        if ($this->type_si == 'manual-payment-entry') {
            $po = PurchaseBilling::find($this->ref_id, ['gp_payment_number']);
            if ($po) {
                return $po->gp_payment_number ?? '-';
            }
        }

        if (in_array($this->type_si, ['order-manual', 'transaction-agent'])) {
            $order = OrderManual::find($this->ref_id, ['gp_si_number']);
            if ($order) {
                return $order->gp_si_number ?? '-';
            }
        }

        if (in_array($this->type_si, ['telmark', 'trx_general'])) {
            $order = Transaction::find($this->ref_id, ['gp_submit_number']);
            if ($order) {
                return $order->gp_submit_number ?? '-';
            }
        }


        return '-';
    }

    public function getCreatedAtFormattedAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-m-Y H:i:s');
    }
}
