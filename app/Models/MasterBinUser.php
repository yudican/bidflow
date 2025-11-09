<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBinUser extends Model
{
    use HasFactory;
    protected $fillable = ['master_bin_id', 'user_id', 'status'];

    protected $dates = [];

    /**
     * Get the masterBin that owns the MasterBinUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterBin()
    {
        return $this->belongsTo(MasterBin::class);
    }

    /**
     * Get the user that owns the MasterBinUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name');
    }
}
