<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductConvertDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_convert_id',
        'sku',
        'produk_nama',
        'qty',
        'toko',
        'harga_awal',
        'harga_promo',
        'harga_satuan',
        'ongkir',
        'tanggal_transaksi',
        'subtotal',
        'status_convert',
    ];

    /**
     * Get the productSku associated with the ProductConvertDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function productSku()
    {
        return $this->hasOne(ProductSku::class, 'sku', 'sku');
    }
}
