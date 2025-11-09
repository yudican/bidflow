<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionAwb extends Model
{
    use HasFactory;
    protected $table = 'transactions_awb';

    protected $fillable = [
        'id_transaksi',
        'awb_number',
    ];
}
