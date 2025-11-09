<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDeliveryStatus extends Model
{
    use HasFactory;
    protected $table = 'transactions_delivery_status';

    protected $fillable = [
        'id_transaksi',
        'delivery_status',
    ];
}
