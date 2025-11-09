<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    use HasFactory;
    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'role',
        'user_id',
    ];

    /**
     * Get the user that owns the TeamUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
