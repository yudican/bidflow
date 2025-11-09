<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommisionWithdraw extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'request_by',
        'role_id',
        'email',
        'phone',
        'amount',
        'status',
        'nama_rekening',
        'nomor_rekening',
        'nama_bank',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    protected $appends = ['user_name', 'role_name', 'request_by_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestBy()
    {
        return $this->belongsTo(User::class, 'request_by');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function commisionWithdrawApprovals()
    {
        return $this->hasMany(CommisionWithdrawApproval::class);
    }

    public function getUserNameAttribute()
    {
        $user = User::find($this->user_id);
        return $user ? $user->name : '-';
    }

    public function getRoleNameAttribute()
    {
        $role = Role::find($this->role_id);
        return $role ? $role->role_name : '-';
    }

    public function getRequestByNameAttribute()
    {
        $user = User::find($this->request_by);
        return $user ? $user->name : '-';
    }
}
