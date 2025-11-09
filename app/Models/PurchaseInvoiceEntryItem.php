<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceEntryItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_invoice_entry_id',
        'purchase_order_id',
        'purchase_order_item_id',
        'product_id',
        'uom',
        'tax_code',
        'qty',
        'extended_cost',
        'product_name',
        'status_gp',
        'gp_payment_number',
        'sku'
    ];


    protected $appends = ['po_number', 'product_name', 'sku', 'received_number', 'ppn', 'amount_with_ppn', 'subtotal', 'total'];

    function getPoNumberAttribute()
    {
        $purchase = PurchaseOrder::find($this->purchase_order_id, ['po_number']);
        return $purchase ? $purchase->po_number : '-';
    }

    function getReceivedNumberAttribute()
    {
        $purchase = PurchaseOrderItem::find($this->purchase_order_item_id, ['received_number']);
        return $purchase ? $purchase->received_number : '-';
    }

    function getProductNameAttribute()
    {
        if ($this->attributes['product_name']) {
            return $this->attributes['product_name'];
        }
        $product = Product::find($this->product_id, ['name']);
        return $product ? $product->name : '-';
    }

    /**
     * Get the invoiceEntry that owns the PurchaseInvoiceEntryItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoiceEntry()
    {
        return $this->belongsTo(PurchaseInvoiceEntry::class, 'purchase_invoice_entry_id');
    }

    public function getSkuAttribute()
    {
        // if (@$this->attributes['sku']) {
        //     return $this->attributes['sku'];
        // }
        $product = Product::where('id', $this->product_id)->first();
        return $product ? $product->sku : $this->attributes['sku'];
    }

    public function getPpnAttribute()
    {
        $invoice = PurchaseInvoiceEntry::find($this->purchase_invoice_entry_id);    
        if (!empty($invoice->tax_id)) {
            $tax = MasterTax::find($invoice->tax_id);
            $ppn = $this->extended_cost * @$tax->tax_percentage/100;
        } else {
            $ppn = null;
        }
        return $ppn;
    }

    public function getAmountWithPpnAttribute()
    {
        if ($this->ppn > 0) {
            $ppn =  $this->extended_cost * $this->ppn;
        } else {
            $ppn =  $this->extended_cost;
        }
        
        return $ppn;
    }

    public function getSubtotalAttribute()
    {
        $subtotal =  $this->extended_cost * $this->qty;
        return $subtotal;
    }

    public function getTotalAttribute()
    {
        $total =  $this->subtotal + $this->ppn;
        return $total;
    }
}
