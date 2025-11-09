<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_order_id', 'product_id', 'tax_id', 'qty', 'qty_diterima', 'uom', 'price', 'status', 'received_number', 'gp_received_number', 'do_number', 'invoice_entry', 'notes', 'vendor_doc_number', 'confirm_by', 'invoice_date', 'due_date', 'is_allocated', 'uid_invoice', 'qty_alocation', 'ref', 'is_master', 'status_gp', 'received_date',
    ];

    protected $appends = ['prices', 'subtotal', 'tax_amount', 'tax_percentage', 'total_amount', 'tax_product_received', 'subtotal_product_received', 'subtotal_qty_diterima', 'sku', 'u_of_m', 'type', 'product_name', 'confirm_by_name', 'tax_invoice', 'qty_can_allocated', 'can_cancel',];

    /**
     * Get the purchaseOrder that owns the PurchaseOrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }



    /**
     * Get the product that owns the PurchaseOrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product that owns the PurchaseOrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productAdditional()
    {
        return $this->belongsTo(ProductAdditional::class, 'product_id');
    }

    /**
     * Get the tax that owns the PurchaseOrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tax()
    {
        return $this->belongsTo(MasterTax::class, 'tax_id');
    }

    public function getQtyCanAllocatedAttribute()
    {
        $qty = $this->qty_diterima - $this->qty_alocation;
        return $qty;
    }

    public function getCanCancelAttribute()
    {
        $inventory = InventoryProductStock::where('uid_inventory', $this->ref)->whereInventoryType('received')->whereInventoryStatus('received')->select('uid_inventory')->first();
        if ($inventory) {
            return true;
        }

        return false;
    }

    public function getTypeAttribute()
    {
        $purchase = PurchaseOrder::find($this->purchase_order_id);
        if ($purchase) {
            return $purchase->type_po;
        }
        return '-';
    }

    public function getConfirmByNameAttribute()
    {
        $user = User::find($this->confirm_by);
        return $user?->name ?? '-';
    }

    public function getProductNameAttribute()
    {
        if ($this->type == 'product') {
            $product = Product::find($this->product_id);
            return $product?->name ?? '-';
        }

        $product = ProductAdditional::find($this->product_id);
        return $product?->name ?? '-';
    }

    public function getPricesAttribute()
    {
        // if ($this->type == 'product') {
        //     if ($this->product) {
        //         $price = $this->product->price['final_price'];
        //         return $price;
        //     }

        //     return 0;
        // }

        $price = $this->price;
        return $price;
    }

    public function getSubtotalAttribute()
    {
        $price = $this->prices;
        $subtotal = 0;
        $subtotal = $price * $this->qty;
        return $subtotal;
    }

    public function getTaxPercentageAttribute()
    {
        $tax_amount = 0;
        if ($this->tax) {
            $tax_amount = $this->tax->tax_percentage;
        }
        return $tax_amount;
    }

    public function getTaxAmountAttribute()
    {
        $tax_amount = 0;
        if ($this->tax) {
            $tax  =  $this->subtotal * $this->tax->tax_percentage / 100;
            $tax_amount = $tax;
        }
        return round($tax_amount);
    }

    public function getTotalAmountAttribute()
    {
        $total_amount = $this->subtotal;
        if ($this->tax) {
            $tax  =  $this->subtotal * $this->tax->tax_percentage / 100;
            $total_amount = $this->subtotal + $tax;
        }
        return round($total_amount);
    }

    public function getSubtotalQtyDiterimaAttribute()
    {
        $price = $this->prices;
        $subtotal = 0;
        $subtotal = $price * $this->qty_diterima;
        return $subtotal;
    }

    public function getSkuAttribute()
    {
        if ($this->type == 'product') {
            $product = Product::find($this->product_id);
            return $product->sku ?? '-';
        }

        $product = ProductAdditional::find($this->product_id);
        return $product->sku ?? '-';
    }

    public function getUOfMAttribute()
    {
        if ($this->type == 'product') {
            $sku_master = SkuMaster::where('sku', $this->sku)->where('status', 1)->first();
            return $sku_master?->package_name ?? '-';
        }

        return $this->uom ?? '-';
    }

    public function getTaxInvoiceAttribute()
    {
        $tax_percentage = $this->tax_percentage;
        if ($tax_percentage > 0) {
            $price = $this->price;
            $tax = $price * $tax_percentage / 100;
            $tax_invoice = $price + $tax;

            return round($tax_invoice);
        }

        return 0;
    }

    public function getTaxProductReceivedAttribute()
    {
        $tax_percentage = $this->tax_percentage;
        if ($tax_percentage > 0) {
            $price = $this->price * $this->qty_diterima;
            $tax = $price * $tax_percentage / 100;

            return round($tax);
        }

        return 0;
    }

    public function getSubtotalProductReceivedAttribute()
    {
        $price = $this->price * $this->qty_diterima;
        $tax = $this->tax_product_received;
        $tax_invoice = $price + $tax;

        return round($tax_invoice);
    }
}
