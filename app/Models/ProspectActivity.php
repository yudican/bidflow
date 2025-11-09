<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectActivity extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'prospect_id',
        'notes',
        'status',
        'attachment',
        'submit_date',
    ];


    /**
     * Get the prospect that owns the ProspectActivity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
