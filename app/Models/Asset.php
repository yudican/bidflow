<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = [
        'po_number',
        'brand_name'
    ];

    public function getPoNumberAttribute()
    {
        $po = PurchaseOrder::find($this->purchase_order_id, ['po_number']);
        return $po ? $po->po_number : '-';
    }

    public function getBrandNameAttribute()
    {
        $brand = Brand::find($this->brand_id);
        return $brand?->name ?? '-';
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function logs()
    {
        return $this->hasMany(AssetControlLog::class);
    }

    /**
     * Get the ownerUser that owns the Asset
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ownerUser()
    {
        return $this->belongsTo(User::class, 'owner');
    }
}
