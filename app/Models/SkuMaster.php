<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuMaster extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['sku', 'package_id', 'status', 'expired_at'];

    protected $dates = [];

    protected $appends = ['package_name'];

    /**
     * Get the package that owns the SkuMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function getPackageNameAttribute()
    {
        return $this->package ? $this->package->name : 'Pcs';
    }
}
