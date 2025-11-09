<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['nama_bank', 'nomor_rekening_bank', 'nama_rekening_bank', 'logo_bank', 'status', 'parent_id', 'payment_type', 'payment_channel', 'payment_code', 'payment_va_number'];

    protected $dates = [];

    protected $appends = ['logo'];

    /**
     * Get the parent that owns the PaymentMethod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(PaymentMethod::class, 'parent_id')->where('parent_id', null)->with('parent');
    }

    /**
     * Get all of the children for the PaymentMethod
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(PaymentMethod::class, 'parent_id')->with('children');
    }

    public function getLogoAttribute()
    {
        return getImage($this->logo_bank);
    }
}
