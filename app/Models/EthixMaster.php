<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EthixMaster extends Model
{
    use HasFactory;

    protected $appends = ['description'];


    function getDescriptionAttribute()
    {
        $awb_number = $this->awb_number;
        $status = $this->status;
        if ($awb_number) {
            return "Resi Telah Diinput - [$awb_number]";
        }

        if ($status) {
            return "Update Status Pesanan - [$status]";
        }

        return '-';
    }
}
