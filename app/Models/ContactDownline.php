<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactDownline extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'company_id'];

    protected $appends = ['userData'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getUserDataAttribute()
    {
        $user = $this->company?->user;
        if ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->telepon,
            ];
        }
        return null;
    }
}
