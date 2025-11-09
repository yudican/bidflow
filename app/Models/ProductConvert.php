<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductConvert extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['user_name', 'success', 'failed'];
    /**
     * Get the user that owns the ProductConvert
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'convert_user_id');
    }

    public function getUserNameAttribute()
    {
        $user = User::find($this->convert_user_id);

        return $user?->name ?? '-';
    }

    public function getSuccessAttribute()
    {
        $success = ProductConvertDetail::where('product_convert_id', $this->id)->where('status_convert', 1)->count();

        return $success;
    }

    public function getFailedAttribute()
    {
        $success = ProductConvertDetail::where('product_convert_id', $this->id)->where('status_convert', 0)->count();

        return $success;
    }
}
