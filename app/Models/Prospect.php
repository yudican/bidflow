<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'prospect_number',
        'contact',
        'created_by',
        'status',
        'tag',
    ];
    protected $appends = ['contact_name', 'created_by_name', 'tag_name', 'activity_total'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prospect) {
            $latestProspect = self::latest()->first();

            $currentYear = now()->format('Y');
            $sequenceNumber = $latestProspect ? (int)substr($latestProspect->prospect_number, -3) + 1 : 1;

            $prospect->prospect_number = 'PROSPECT/SA' . str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;
        });
    }

    /**
     * Get all of the activities for the Prospect
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(ProspectActivity::class, 'prospect_id');
    }

    public function getActivityTotalAttribute()
    {
        return $this->activities()->count();
    }

    public function getContactNameAttribute()
    {
        $user = User::find($this->contact, ['name']);

        if ($user) {
            return $user->name;
        }

        return '-';
    }


    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by, ['name']);

        if ($user) {
            return $user->name;
        }

        return '-';
    }

    public function getTagNameAttribute()
    {
        $count = $this->activities()->count();
        if ($count <= 4) {
            return '❄️ Cold';
        } else if ($count > 5 && $count <= 7) {
            return '🌤 Warm';
        } else if ($count >= 6) {
            return '🔥 Hot';
        }

        return '❄️ Cold';
    }
}
