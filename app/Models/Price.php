<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['level_id', 'product_id', 'product_variant_id', 'basic_price', 'final_price'];

    protected $dates = [];

    /**
     * Get the level that owns the Price
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
