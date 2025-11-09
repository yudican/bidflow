<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturMaster extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['uid_retur', 'name', 'email', 'handphone', 'phone', 'address', 'type_case', 'alasan', 'transaction_from', 'transaction_id', 'transfer_photo'];

    protected $dates = [];

    protected $appends = ['status_return', 'transfer_photo_url'];

    /**
     * Get all of the refundRekening for the RefundMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function returRekenings()
    {
        return $this->hasMany(ReturRekening::class, 'uid_retur', 'uid_retur');
    }

    /**
     * Get all of the returRekening for the RefundMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function returItems()
    {
        return $this->hasMany(ReturItem::class, 'uid_retur', 'uid_retur');
    }

    /**
     * Get all of the returRekening for the RefundMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function returResis()
    {
        return $this->hasMany(ReturResi::class, 'uid_retur', 'uid_retur');
    }

    public function getStatusReturnAttribute()
    {
        return getStatusRetur($this->status);
    }

    public function getTransferPhotoUrlAttribute()
    {
        return $this->transfer_photo ? getImage($this->transfer_photo) : asset('assets/images/no-image.png');
    }
}
