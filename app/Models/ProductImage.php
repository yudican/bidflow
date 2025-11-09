<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;
    // fillable
    protected $guarded = [];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->name ? getImage($this->name) : null;
    }
}
