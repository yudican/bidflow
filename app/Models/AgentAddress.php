<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAddress extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'agent_address';

    /**
     * Get the agent that owns the AgentAddress
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(AgentDetail::class, 'user_id', 'user_id');
    }
}
