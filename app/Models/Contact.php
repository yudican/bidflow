<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'users';
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'telepon',
        'brand_id',
        'username',
        'password',
        'bod',
        'gender',
        'profile_photo_path',
        'created_by',
        'dark_mode'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'created_by_name',
        'role_name'
    ];

    /**
     * The roles that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    public function getRoleNameAttribute()
    {
        $role = $this->roles()->first();
        if ($role) {
            return $role->role_name;
        }
        return '-';
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user ? $user->name : '-';
    }
}
