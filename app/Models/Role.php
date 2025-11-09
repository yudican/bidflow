<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use Uuid;
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['role_type', 'role_name', 'rate_limit_status'];

    protected $dates = [];

    protected $casts = [
        'user_id' => 'string'
    ];

    /**
     * The users that belong to the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The permissions that belong to the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * The menus that belong to the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class);
    }

    // many to many with NotificationTemplateRole
    public function notificationTemplates()
    {
        return $this->belongsToMany(NotificationTemplate::class, 'notification_template_role', 'role_id', 'notification_template_id');
    }

    /**
     * The levels that belong to the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function levels()
    {
        return $this->belongsToMany(Level::class, 'level_role');
    }
}
