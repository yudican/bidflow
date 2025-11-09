<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['title', 'url', 'image', 'slug', 'description', 'brand_id', 'status', 'sales_channel'];

    protected $dates = [];

    protected $appends = ['brand_ids', 'banner_image', 'brand_name'];

    // belongsTo Brand (one to one) model
    function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * The brands that belong to the Banner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'banner_brand');
    }

    public function getBrandIdsAttribute()
    {
        if ($this->brands()->count() > 0) {
            return $this->brands()->pluck('brands.id')->toArray();
        }

        return $this->brand?->id ?? 1;
    }

    public function getBannerImageAttribute()
    {
        return getImage($this->image);
    }

    public function getBrandNameAttribute()
    {
        if ($this->brands()->count() > 0) {
            return $this->brands()->pluck('brands.name')->implode(',');
        }

        return $this->brand?->name ?? '-';
    }
}
