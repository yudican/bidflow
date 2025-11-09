<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_number',
        'vendor_doc_number',
        'invoice_date',
        'created_by',
        'vendor_id',
        'vendor_name',
        'batch_id',
        'payment_term_id',
        'submit_gp',
        'status',
        'type_invoice',
        'status_gp',
        'status_payable_gp',
        'gp_invoice_number',
        'gp_payable_number',
        'tax_id'
    ];

    protected $appends = ['created_by_name', 'payment_term_name', 'amount_to_pay', 'amount_payment', 'status_name', 'submit_payment_gp'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prospect) {
            $latestProspect = self::latest()->first();

            $currentYear = now()->format('Y');
            $sequenceNumber = $latestProspect ? (int)substr($latestProspect->received_number, -9) + 1 : 1;

            $prospect->received_number = 'RCV/' . $currentYear . '/' . str_pad($sequenceNumber, 9, '0', STR_PAD_LEFT);
        });
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user ? $user->name : '-';
    }

    public function getPaymentTermNameAttribute()
    {
        $payment_term = PaymentTerm::find($this->payment_term_id);
        return $payment_term?->name ?? '-';
    }

    public function getSubmitPaymentGpAttribute()
    {
        $purchase = PurchaseBilling::where('received_number', $this->received_number)->where('status', 1)->first();
        return $purchase?->status_gp ?? '-';
    }

    /**
     * Get all of the billings for the PurchaseInvoiceEntry
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function billings()
    {
        return $this->hasMany(PurchaseBilling::class, 'received_number', 'received_number');
    }

    /**
     * Get all of the items for the PurchaseInvoiceEntry
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(PurchaseInvoiceEntryItem::class, 'purchase_invoice_entry_id');
    }

    function getAmountToPayAttribute()
    {
        $amount = 0;

        foreach ($this->items as $key => $value) {
            $amount += @$value->price * @$value->qty;
        }

        return $amount;
    }


    function getAmountPaymentAttribute()
    {
        $amount = $this->billings()->where('status', 1)->sum('jumlah_transfer');

        return $amount;
    }


    function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'UNPAID';
            case 1:
                return 'PAID';
            case 2:
                return 'CANCEL';
            case 2:
                return 'PARTIAL PAID';

            default:
                return 'DRAFT';
        }
    }
}
