<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadBilling extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['upload_billing_photo_url', 'upload_transfer_photo_url', 'approved_by_name'];

    protected $with = ['orderProductBillings'];

    public function getUploadBillingPhotoUrlAttribute()
    {
        return $this->upload_billing_photo ? getImage($this->upload_billing_photo) : null;
    }

    public function getUploadTransferPhotoUrlAttribute()
    {
        return $this->upload_transfer_photo ? getImage($this->upload_transfer_photo) : null;
    }

    /**
     * Get the leadMaster that owns the LeadBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderLead()
    {
        return $this->belongsTo(OrderLead::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get the orderManual that owns the LeadBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderManual()
    {
        return $this->belongsTo(OrderManual::class, 'uid_lead', 'uid_lead');
    }

    public function getApprovedByNameAttribute()
    {
        return $this->approved_by ? User::find($this->approved_by)->name : '-';
    }

    /**
     * Get the salesRetur that owns the LeadBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesRetur()
    {
        return $this->belongsTo(SalesReturn::class, 'uid_lead', 'uid_retur');
    }

    /**
     * Get all of the orderBillings for the LeadBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderProductBillings()
    {
        return $this->hasMany(OrderProductBilling::class, 'billing_id');
    }
}
