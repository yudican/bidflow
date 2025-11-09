<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['name', 'description', 'status'];

    protected $dates = [];

    protected $appends = ['role_ids'];

    /**
     * The roles that belong to the Level
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'level_role');
    }

    public function getRoleIdsAttribute()
    {
        return $this->roles->pluck('id')->toArray();
    }
}
