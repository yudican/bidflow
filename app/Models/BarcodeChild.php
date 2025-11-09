<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeChild extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "barcode_childs";

    public function barcodeMaster()
    {
        return $this->belongsTo(BarcodeMaster::class, 'parent_id');
    }
}
