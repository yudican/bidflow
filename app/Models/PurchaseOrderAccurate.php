<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderAccurate extends Model
{
    use HasFactory;
    protected $fillable = [
        'po_number',
        'vendor_code',
        'vendor_name',
        'amount',
        'branch_name',
        'createdby',
        'created_date',
        'payment_term',
        'status_po',
        'send_api_orca',
        'date_send_api',
        'status'
    ];


    // protected $appends = [
    //     'po_number', 'vendor_code', 'vendor_name', 'amount', 'branch_name', 'created_by', 'created_date', 'payment_term', 'status_po', 'send_api_orca', 'date_send_api', 'status'
    // ];

    public function getPoNumberAttribute()
    {
        return $this->attributes['po_number'];
    }

    // public function getVendorCodeAttribute()
    // {
    //     return $this->attributes['vendor_code'];
    // }

    // public function getVendorNameAttribute()
    // {
    //     return $this->attributes['vendor_name'];
    // }

    // public function getAmountAttribute()
    // {
    //     return $this->attributes['amount'];
    // }

    // public function getBranchNameAttribute()
    // {
    //     return $this->attributes['branch_name'] ?? null;
    // }

    // public function getCreatedByAttribute()
    // {
    //     return $this->attributes['created_by'] ?? null;
    // }

    // public function getCreatedDateAttribute()
    // {
    //     return $this->attributes['created_date'] ?? null;
    // }

    // public function getPaymentTermAttribute()
    // {
    //     return $this->attributes['payment_term'] ?? null;
    // }

    // public function getStatusPoAttribute()
    // {
    //     return $this->attributes['status_po'] ?? null;
    // }

    // public function getSendApiOrcaAttribute()
    // {
    //     return $this->attributes['send_api_orca'] ?? null;
    // }

    // public function getDateSendApiAttribute()
    // {
    //     return $this->attributes['date_send_api'] ?? null;
    // }

    // public function getStatusAttribute()
    // {
    //     return $this->attributes['status'] ?? null;
    // }

    // public function createdBy()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }

    public function barcodes()
    {
        return $this->hasMany(Asset::class);
    }

    public function barcodelogs()
    {
        return $this->hasMany(BarcodeSubmitLog::class, 'po_id');
    }
}
