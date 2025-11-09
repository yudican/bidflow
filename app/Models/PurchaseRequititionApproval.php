<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequititionApproval extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_requitition_id',
        'user_id',
        'role_id',
        'status',
        'label'
    ];

    protected $appends = [
        'user_name',
        'role_name',
        'role_type'
    ];

    public function purchaseRequitition()
    {
        return $this->belongsTo(PurchaseRequitition::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getUserNameAttribute()
    {
        $user = User::find($this->user_id, ['name']);
        return $user ? $user->name : '-';
    }

    public function getRoleNameAttribute()
    {
        $role = Role::find($this->role_id, ['role_name']);
        return $role ? $role->role_name : '-';
    }

    public function getRoleTypeAttribute()
    {
        $role = Role::find($this->role_id, ['role_type']);
        return $role ? $role->role_type : '-';
    }
}
