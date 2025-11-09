<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['created_by_name'];

    public function barcodeMaster()
    {
        return $this->belongsTo(BarcodeMaster::class, 'barcode_id');
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user?->name ?? '-';
    }
}
