<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImportTemp extends Model
{
    use HasFactory;
    protected $fillable  = [
        'trx_id',
        'user',
        'channel',
        'toko',
        'sku',
        'produk_nama',
        'harga_awal',
        'harga_promo',
        'qty',
        'ongkir',
        'metode_pembayaran',
        'diskon',
        'tanggal_transaksi',
        'kurir',
        'resi',
        'status',
        'user_id',
        'status_import',
        'status_convert',
    ];
}
