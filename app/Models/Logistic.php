<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logistic extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['logistic_name', 'logistic_url_logo', 'logistic_status', 'logistic_original_id', 'logistic_type', 'logistic_shipping_type'];

    protected $dates = [];

    /**
     * Get all of the logisticRates for the Logistic
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logisticRates()
    {
        return $this->hasMany(LogisticRate::class);
    }

    /**
     * Get all of the masterOngkirs for the Logistic
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function masterOngkirs()
    {
        return $this->belongsToMany(MasterOngkir::class, 'ongkir_logistic', 'logistic_id', 'master_ongkir_id');
    }
}
