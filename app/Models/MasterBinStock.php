<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBinStock extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['bin_name'];

    protected static function booted()
    {
        static::addGlobalScope('latest_stock', function (Builder $builder) {
            $builder->where('stock_type', 'new');
        });
    }

    /**
     * Get the masterBin that owns the MasterBinStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterBin()
    {
        return $this->belongsTo(MasterBin::class, 'master_bin_id');
    }

    public function getBinNameAttribute()
    {
        $masterBin = $this->masterBin;

        return $masterBin ? $masterBin->name : '-';
    }
}
