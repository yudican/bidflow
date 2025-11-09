<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarginBottom extends Model
{
    //use Uuid;
    use HasFactory;

    protected $table = 'product_margin_bottoms';

    //public $incrementing = false;

    protected $fillable = ['product_id', 'basic_price', 'role_id', 'margin', 'description', 'status', 'product_variant_id'];

    protected $dates = [];

    protected $appends = ['product_name', 'product_image', 'role_name'];

    /**
     * Get the productVariant associated with the MarginBottom
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the role that owns the MarginBottom
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getProductNameAttribute()
    {
        if ($this->productVariant) {
            return $this->productVariant->name;
        }

        return '-';
    }

    public function getProductImageAttribute()
    {
        if ($this->productVariant) {
            return getImageUrl($this->productVariant->image);
        }

        return '-';
    }

    public function getRoleNameAttribute()
    {
        if ($this->role) {
            return $this->role->role_name;
        }

        return '-';
    }
}
