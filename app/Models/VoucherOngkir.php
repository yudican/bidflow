<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherOngkir extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $guarded = [];

    protected $dates = ['start_date', 'end_date'];
    protected $appends = ['brand_ids', 'brand_name', 'voucher_image'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * The brands that belong to the Voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_voucher');
    }

    public function getBrandIdsAttribute()
    {
        if ($this->brands()->count() > 0) {
            return $this->brands()->pluck('brands.id')->toArray();
        }

        return 1;
    }

    public function getBrandNameAttribute()
    {
        if ($this->brands()->count() > 0) {
            return $this->brands()->pluck('brands.name')->implode(',');
        }

        return '-';
    }

    public function getVoucherImageAttribute()
    {
        if ($this->image) {
            return getImage($this->image);
        }

        return null;
    }
}
