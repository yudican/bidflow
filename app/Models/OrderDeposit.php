<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDeposit extends Model
{
    use HasFactory;

    protected $fillable = ['uid_lead', 'order_type', 'contact', 'amount', 'description'];

    protected $dates = [];

    protected $appends = ['contact_name'];

    /**
     * Get the contact that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contactUser()
    {
        return $this->belongsTo(User::class, 'contact');
    }

    public function getContactNameAttribute()
    {
        return $this->contactUser->name;
    }

    /**
     * Get the orderLead that owns the OrderDeposit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderLead()
    {
        return $this->belongsTo(OrderLead::class, 'uid_lead', 'uid_lead')->where('order_type', 'lead');
    }

    /**
     * Get the orderManual that owns the OrderDeposit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderManual()
    {
        return $this->belongsTo(OrderManual::class, 'uid_lead', 'uid_lead')->where('order_type', 'manual');
    }
}
