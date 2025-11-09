<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['name', 'address', 'email', 'phone', 'brand_id', 'owner_name', 'nik', 'owner_phone', 'pic_name', 'pic_phone', 'user_id', 'status', 'business_entity', 'layer_type', 'npwp', 'npwp_name', 'nib', 'file_nib', 'need_faktur'];

    protected $dates = [];

    /**
     * Get the businessEntity that owns the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_entity');
    }

    /**
     * Get the user that owns the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
