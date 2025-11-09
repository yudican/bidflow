<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['uid_lead', 'title', 'description', 'start_date', 'end_date', 'result', 'attachment', 'status', 'user_created', 'user_updated', 'geo_tagging', 'latitude', 'longitude'];

    protected $dates = ['start_date', 'end_date'];

    protected $appends = ['attachment_url'];
    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'user_created');
    }

    /**
     * Get the leadMaster that owns the LeadActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadMaster()
    {
        return $this->belongsTo(LeadMaster::class, 'uid_lead', 'uid_lead');
    }

    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return getImageUrl($this->attachment);
        }
        return null;
    }
}
