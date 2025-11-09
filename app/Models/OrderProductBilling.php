<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProductBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid_lead',
        'billing_id',
        'product_id',
        'qty_billing',
    ];


    /**
     * Get the orderBilling that owns the OrderProductBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderBilling()
    {
        return $this->belongsTo(LeadBilling::class, 'billing_id');
    }

    /**
     * Get the product that owns the OrderProductBilling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id');
    }
}
