<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpBatchId extends Model
{
    use HasFactory;
    protected $table = 'gp_batch_id';

    protected $fillable = [
        'batch_code',
        'origin',
        'status',
        'frequency',
    ];
}
