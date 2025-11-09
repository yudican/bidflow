<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function barcodeChildren()
    {
        return $this->hasMany(BarcodeChild::class, 'parent_id');
    }

    public function barcodeHistory()
    {
        return $this->hasMany(BarcodeHistory::class, 'barcode_id');
    }
}
