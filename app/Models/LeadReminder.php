<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadReminder extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['contactUser'];

    /**
     * Get the userContact that owns the LeadReminder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userContact()
    {
        return $this->belongsTo(User::class, 'contact');
    }

    public function getContactUserAttribute()
    {
        if ($this->userContact) {
            return [
                'label' => $this->userContact->name,
                'value' => $this->userContact->id,
            ];
        }
        return null;
    }
}
