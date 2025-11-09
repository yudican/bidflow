<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionStatus extends Model
{
    use HasFactory;
    protected $table = 'transactions_status';

    protected $fillable = [
        'id_transaksi',
        'status',
    ];
}
