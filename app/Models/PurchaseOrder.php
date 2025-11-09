<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'created_by',
        'submit_by',
        'payment_term_id',
        'warehouse_id',
        'warehouse_user_id',
        'company_id',
        'po_number',
        'gp_po_number',
        'vendor_code',
        'currency',
        'notes',
        'status',
        'received_by',
        'rejected_by',
        'rejected_reason',
        'approved_by',
        'vendor_name',
        'type_po',
        'channel',
        'stock_opname',
        'status_ethix',
        'status_gp',
        'tax_id',
        'status_ethix_submit',
        'pr_type',
        'pr_number',
        'brand_id',
        'has_barcode',
        'hit_barcode',
        'hit_user'
    ];


    protected $appends = [
        'brand_name', 'payment_term_name', 'warehouse_name', 'warehouse_address', 'warehouse_user_name', 'company_name', 'created_by_name', 'subtotal', 'tax', 'tax_amount', 'tax_product_received', 'total_amount', 'amount', 'received_by_name', 'rejected_by_name', 'total', 'total_approved', 'total_tax', 'total_qty_diterima', 'total_qty_invoice', 'price_total_qty_invoice',
        'subtotal_qty_diterima', 'approved_by_name', 'note_purchase', 'qty_not_allocated', 'amount_to_pay', 'term_days', 'qty_total', 'invoice_entrys', 'tax_invoice', 'total_invoice_amount', 'ethix_items', 'status_ethix_name', 'submit_by_name'
    ];


    /**
     * Get the createdBy that owns the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all of the items for the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function billings()
    {
        return $this->hasMany(PurchaseBilling::class);
    }

    public function barcodes()
    {
        return $this->hasMany(Asset::class);
    }

    public function barcodelogs()
    {
        return $this->hasMany(BarcodeSubmitLog::class, 'po_id');
    }

    public function requisitions()
    {
        return $this->hasMany(PurchaseRequitition::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get all of the inevntoryStock for the PurchaseOrderItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventoryStock()
    {
        return $this->hasMany(InventoryProductStock::class, 'reference_number', 'po_number');
    }

    /**
     * Get the taxData that owns the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxData()
    {
        return $this->belongsTo(MasterTax::class, 'tax_id');
    }

    public function getBrandNameAttribute()
    {
        $brand = Brand::find($this->brand_id);
        return $brand?->name ?? '-';
    }

    public function getPaymentTermNameAttribute()
    {
        $payment_term = PaymentTerm::find($this->payment_term_id);
        return $payment_term?->name ?? '-';
    }

    public function getTermDaysAttribute()
    {
        $payment_term = PaymentTerm::find($this->payment_term_id);
        return $payment_term?->days_of ?? 1;
    }

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id);
        return $warehouse?->name ?? '-';
    }

    public function getWarehouseAddressAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id);
        return $warehouse?->alamat ?? '-';
    }

    public function getWarehouseUserNameAttribute()
    {
        $warehouse_user = User::find($this->warehouse_user_id);
        return $warehouse_user?->name ?? '-';
    }

    public function getCompanyNameAttribute()
    {
        $company = CompanyAccount::find($this->company_id);
        return $company?->account_name ?? '-';
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user?->name ?? '-';
    }

    public function getSubmitByNameAttribute()
    {
        $user = User::find($this->submit_by);
        return $user?->name ?? '-';
    }

    public function getApprovedByNameAttribute()
    {
        $user = User::find($this->approved_by);
        return $user?->name ?? '-';
    }

    public function getReceivedByNameAttribute()
    {
        $user = User::find($this->received_by);
        return $user?->name ?? '-';
    }

    public function getRejectedByNameAttribute()
    {
        $user = User::find($this->rejected_by);
        return $user?->name ?? '-';
    }

    public function getSubtotalAttribute()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            if ($item->is_master > 0) {
                $subtotal += $item->subtotal;
            }
        }
        return $subtotal;
    }

    public function getAmountToPayAttribute()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            if ($item->invoice_entry == 1) {
                $subtotal += $item->subtotal_qty_diterima;
            }
        }
        return $subtotal;
    }
    public function getQtyTotalAttribute()
    {
        $subtotal = 0;
        foreach ($this->items()->where('is_master', 1)->get() as $item) {
            $subtotal += $item->qty;
        }
        return $subtotal;
    }

    public function getTaxAttribute()
    {
        $tax_amount = 0;
        if ($this->taxData) {
            $tax = $this->taxData;
            $tax_amount = $tax->tax_percentage;
        }

        return $tax_amount;
    }

    public function getTaxAmountAttribute()
    {
        $price_amount = 0;
        foreach ($this->items as $item) {
            if ($item->is_master > 0) {
                $price_amount += $item->price * $item->qty;
            }
        }
        $tax_percen = intval($this->tax) / 100;
        $tax_price = $price_amount * $tax_percen;
        return $tax_price > 0 ? $tax_price : 0;
    }

    public function getTaxProductReceivedAttribute()
    {
        $tax_amount = 0;
        foreach ($this->items as $item) {
            if ($item->status > 0) {
                $tax_amount += $item->tax_amount;
            }
        }
        return $tax_amount;
    }

    public function getTotalAmountAttribute()
    {
        $total_amount = 0;
        foreach ($this->items()->groupBy('product_id')->get() as $item) {
            $total_amount += $item->total_amount;
        }
        return $total_amount + $this->tax_amount;
    }

    public function getTotalAttribute()
    {
        $total = 0;
        foreach ($this->billings as $item) {
            $total += $item->jumlah_transfer;
        }
        return $total;
    }

    public function getTotalApprovedAttribute()
    {
        $total_approved = 0;
        foreach ($this->billings as $item) {
            if ($item->status == 1) {
                $total_approved += $item->jumlah_transfer;
            }
        }
        return $total_approved + $this->total_tax;
    }

    public function getTotalTaxAttribute()
    {
        $total_tax = 0;
        foreach ($this->billings as $item) {
            if ($item->status == 1) {
                $total_tax += $item->tax_amount;
            }
        }
        return $total_tax;
    }

    public function getTotalQtyDiterimaAttribute()
    {
        $total_qty_diterima = 0;
        foreach ($this->items as $item) {
            $total_qty_diterima += $item->qty_diterima;
        }
        return $total_qty_diterima;
    }
    // total_qty_invoice
    public function getTotalQtyInvoiceAttribute()
    {
        $total_qty_diterima = 0;
        foreach ($this->items as $item) {
            if ($item->invoice_entry > 0) {
                $total_qty_diterima += $item->qty_diterima;
            }
        }
        return $total_qty_diterima;
    }

    public function getPriceTotalQtyInvoiceAttribute()
    {
        $total_qty_diterima = 0;
        foreach ($this->items as $item) {
            if ($item->invoice_entry > 0) {
                $total_qty_diterima += $item->price * $item->qty_diterima;
            }
        }
        return $total_qty_diterima;
    }

    public function getSubtotalQtyDiterimaAttribute()
    {
        $subtotal_qty_diterima = 0;
        foreach ($this->items as $item) {
            $subtotal_qty_diterima += $item->subtotal_qty_diterima;
        }
        return $subtotal_qty_diterima;
    }
    public function getQtyNotAllocatedAttribute()
    {
        $subtotal_qty_diterima = 0;
        foreach ($this->items()->where('is_allocated', 0)->get() as $item) {
            $subtotal_qty_diterima += $item->qty_diterima;
        }
        return $subtotal_qty_diterima;
    }

    public function getNotePurchaseAttribute()
    {
        if ($this->notes) {
            $payment_term = PaymentTerm::find($this->payment_term_id);
            if (!empty($payment_term->days_of)){
                $notes = str_replace('[days]', "$payment_term->days_of Days", $this->notes);
                return $notes;
            }
            return '-';
        }
        return '-';
    }

    public function getInvoiceEntrysAttribute()
    {
        $invoices = [];
        $items = $this->items()->groupBy('uid_invoice')
            ->select('*')
            ->selectRaw("SUM(price * qty_diterima) as subtotal_invoice")
            ->selectRaw("SUM(qty_diterima) as qty_diterima")
            ->where('invoice_entry', '>', 0)
            ->get();
        foreach ($items as $key => $value) {
            $invoices[$key] = $value;
            if ($value->tax_id) {
                $invoices[$key]['invoice_tax'] = $value->subtotal_invoice * 0.11;
                $invoices[$key]['total_invoice'] = $value->subtotal_invoice + $invoices[$key]['invoice_tax'];
            } else {
                $invoices[$key]['invoice_tax'] = 0;
                $invoices[$key]['total_invoice'] = 0;
            }
        }

        return $invoices;
    }

    public function getTotalInvoiceAmountAttribute()
    {
        $total_invoice = 0;
        foreach ($this->invoice_entrys as $item) {
            $total_invoice += $item->total_invoice;
        }
        return $total_invoice;
    }

    // get amount
    public function getAmountAttribute()
    {
        return $this->subtotal + $this->tax_amount;
    }

    // get tax invoice
    public function getTaxInvoiceAttribute()
    {
        $tax_amount = 0;
        foreach ($this->items as $item) {
            $tax_amount += $item->tax_invoice;
        }
        return $tax_amount;
    }

    public function getEthixItemsAttribute()
    {
        $ethix = EthixMaster::where('po_number', $this->po_number)->get();

        return $ethix;
    }
    public function getStatusEthixNameAttribute()
    {
        $items = $this->items()->whereIn('status_ethix', ['partial', 'finish'])->get();
        foreach ($items as $key => $item) {
            if ($item->status_ethix == 'finish') {
                return 'Sudah Diterima';
            }

            return 'Diterima Sebagian';
        }
    }
}
