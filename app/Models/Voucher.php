<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $guarded = [];

    protected $appends = ['brand_ids', 'brand_name', 'voucher_image', 'voucher_limit'];

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

    public function userVoucher()
    {
        return $this->belongsToMany(User::class, 'user_has_voucher');
    }

    /**
     * Get all of the vouchers for the Voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function getVoucherImageAttribute()
    {
        if ($this->image) {
            return getImage($this->image);
        }

        return null;
    }

    public function getVoucherLimitAttribute()
    {
        $limit = $this->total - $this->userVoucher()->count() - $this->userVouchers()->count();
        return $limit > 0 ? $limit : 0;
    }
}
