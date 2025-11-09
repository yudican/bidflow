<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundMaster extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['uid_refund', 'name', 'email', 'handphone', 'phone', 'address', 'type_case', 'alasan', 'transaction_from', 'transaction_id', 'transfer_photo', 'status', 'is_return'];

    protected $dates = [];

    protected $appends = ['status_refund', 'transfer_photo_url'];

    /**
     * Get all of the refundRekening for the RefundMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refundRekenings()
    {
        return $this->hasMany(RefundRekening::class, 'uid_refund', 'uid_refund');
    }

    /**
     * Get all of the returRekening for the RefundMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refundItems()
    {
        return $this->hasMany(RefundItem::class, 'uid_refund', 'uid_refund');
    }

    /**
     * Get all of the returRekening for the RefundMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refundResis()
    {
        return $this->hasMany(RefundResi::class, 'uid_refund', 'uid_refund');
    }

    public function getStatusRefundAttribute()
    {
        return getStatusRetur($this->status);
    }

    public function getTransferPhotoUrlAttribute()
    {
        return $this->transfer_photo ? getImage($this->transfer_photo) : asset('assets/images/no-image.png');
    }
}
