<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['notification_code', 'notification_title', 'notification_subtitle', 'notification_body', 'notification_type', 'notification_note','group_id','status'];

    protected $dates = [];

    protected $appends = ['role_ids', 'role_names'];

    // many to many with NotificationTemplateRole
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'notification_template_role', 'notification_template_id', 'role_id');
    }

    public function getRoleIdsAttribute()
    {
        return $this->roles()->pluck('roles.id')->toArray();
    }

    public function getRoleNamesAttribute()
    {
        return $this->roles()->pluck('role_name')->toArray();
    }
}
