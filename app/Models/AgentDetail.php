<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent that owns the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(AgentDetail::class, 'parent_id')->where('parent_id', null)->with('parent');
    }

    /**
     * Get all of the children for the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(AgentDetail::class, 'parent_id')->with('children')->orderBy('order', 'ASC');
    }
}
