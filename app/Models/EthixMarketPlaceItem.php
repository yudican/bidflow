<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EthixMarketPlaceItem extends Model
{
    use HasFactory;

    protected $appends = ['product_name'];

    public function getProductNameAttribute()
    {
        $product = Product::where('sku', $this->sku)->first();

        if ($product) {
            return $product->name;
        }

        return '-';
    }
}
