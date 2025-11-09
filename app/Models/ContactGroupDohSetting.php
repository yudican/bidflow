<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactGroupDohSetting extends Model
{
    protected $table = 'contact_group_doh_settings';

    protected $fillable = [
        'contact_group_id',
        'doh_days',
        'notification_type',
        'email_template',
        'whatsapp_template',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'doh_days' => 'integer',
        'contact_group_id' => 'integer'
    ];

    /**
     * Get the contact group that owns this DOH setting
     */
    public function contactGroup(): BelongsTo
    {
        return $this->belongsTo(ContactGroup::class, 'contact_group_id');
    }

    /**
     * Get the user who created this setting
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this setting
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get only active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by DOH days
     */
    public function scopeByDohDays($query, $days)
    {
        return $query->where('doh_days', $days);
    }

    /**
     * Scope to filter by notification type
     */
    public function scopeByNotificationType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Get the template content based on notification type
     */
    public function getTemplateAttribute()
    {
        return $this->notification_type === 'email' 
            ? $this->email_template 
            : $this->whatsapp_template;
    }

    /**
     * Validation rules for DOH days
     */
    public static function getDohDaysOptions()
    {
        return [90, 30, 7];
    }

    /**
     * Validation rules for notification types
     */
    public static function getNotificationTypeOptions()
    {
        return ['email', 'whatsapp'];
    }
}