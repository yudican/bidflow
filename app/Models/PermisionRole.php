<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisionRole extends Model
{
    use HasFactory;

    protected $casts = [
        'role_id' => 'string',
    ];

    protected $table = 'permission_role';
    protected $guarded = [];
    // public $keyType = 'string';

    /**
     * Get the permission that owns the PermisionRole
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the role that owns the PermisionRole
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class, role_id);
    }
}
