<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    //use Uuid;
    use HasFactory;

    protected $table = 'user_datas';
    protected $keyType = 'string';
    //public $incrementing = false;

    protected $fillable = ['level_id', 'class_id', 'customer_id', 'short_name', 'address', 'contact_person', 'city', 'state', 'country', 'zip_code', 'phone1', 'phone2', 'phone3', 'fax', 'npwp', 'status', 'user_id'];

    protected $dates = [];

    /**
     * Get the user that owns the UserData
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
