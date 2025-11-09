<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeSubmitLog extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "barcode_submit_logs";
    protected $appends = ['hit_user_name'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function getHitUserNameAttribute()
    {
        $user = User::find($this->hit_user);
        return $user?->name ?? '-';
    }
}
