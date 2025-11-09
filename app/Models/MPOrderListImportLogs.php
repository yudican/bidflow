<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPOrderListImportLogs extends Model
{
    use HasFactory;
    protected $table = 'mp_order_list_import_logs';
    protected $fillable = [
        'mp_order_list_item_id',
        'status',
        'message',
    ];
}
