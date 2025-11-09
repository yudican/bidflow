<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommisionWithdrawApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'commision_withdraw_id',
        'approved_by',
        'status',
        'note'
    ];

    protected $appends = ['approved_by_name'];

    public function commisionWithdraw()
    {
        return $this->belongsTo(CommisionWithdraw::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getApprovedByNameAttribute()
    {
        $user = User::find($this->approved_by);
        return $user ? $user->name : null;
    }
}
