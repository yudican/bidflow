<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpCheckbook extends Model
{
    use HasFactory;
    protected $fillable = [
        'bank_name',
        'description',
        'company_address',
        'bank_account',
        'currency_id',
        'status',
        'gp_status'
    ];
}
