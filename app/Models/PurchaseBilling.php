<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseBilling extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_order_id',
        'received_number',
        'created_by',
        'approved_by',
        'rejected_by',
        'nama_bank',
        'no_rekening',
        'nama_pengirim',
        'jumlah_transfer',
        'bukti_transfer',
        'tax_amount',
        'status',
        'sumberdana',
        'no_rekening_sumberdana',
        'status_gp',
        'gp_payment_number',
        'payment_number'
    ];

    protected $appends = ['created_by_name', 'approved_by_name', 'rejected_by_name', 'bukti_transfer_url'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user?->name ?? '-';
    }

    public function getApprovedByNameAttribute()
    {
        $user = User::find($this->approved_by);
        return $user?->name ?? '-';
    }

    public function getRejectedByNameAttribute()
    {
        $user = User::find($this->rejected_by);
        return $user?->name ?? '-';
    }

    public function getBuktiTransferUrlAttribute()
    {
        return $this->bukti_transfer ? getImage($this->bukti_transfer) : null;
    }
}
