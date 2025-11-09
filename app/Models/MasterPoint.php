<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPoint extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['point', 'min_trans', 'max_trans', 'nominal', 'percentage', 'product_id', 'type', 'product_sku', 'product_uom'];

    protected $dates = [];

    protected $appends = ['brand_ids', 'brand_name'];

    /**
     * The brands that belong to the MasterPoint
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_master_point');
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
