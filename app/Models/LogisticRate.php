<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticRate extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['logistic_id', 'logistic_rate_code', 'logistic_rate_name', 'logistic_rate_status', 'logistic_cod_status', 'logistic_custommer_status', 'logistic_agent_status', 'logistic_rate_original_id'];

    protected $dates = [];

    /**
     * Get the logistic that owns the LogisticRate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logistic()
    {
        return $this->belongsTo(Logistic::class);
    }

    /**
     * Get the shippingVoucger associated with the LogisticRate
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shippingVoucher()
    {
        return $this->hasOne(ShippingVoucher::class, 'logistic_rate_id');
    }
}
