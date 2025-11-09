<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseItem extends Model
{
    use HasFactory;

    protected $fillable = ['uid_case', 'product_id', 'qty'];

    protected $appends = ['product_name', 'u_of_m', 'sku'];

    public function getProductNameAttribute()
    {
        $product  = ProductVariant::find($this->product_id);

        return $product ? $product->name : '-';
    }

    public function getUOfMAttribute()
    {
        $product = ProductVariant::find($this->product_id);

        return $product ? $product->u_of_m : '-';
    }

    public function getSkuAttribute()
    {
        $product = ProductVariant::find($this->product_id);

        return $product ? $product->sku : '-';
    }
}
