<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['name', 'slug', 'status'];

    protected $dates = [];

    /**
     * Get the product that owns the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
