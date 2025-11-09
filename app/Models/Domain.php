<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['name', 'description', 'status', 'icon', 'url', 'fb_pixel', 'color', 'back_color'];

    protected $dates = [];

    protected $appends = ['icon_url'];

    /**
     * The agents that belong to the Domain
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function agents()
    {
        return $this->belongsToMany(User::class, 'agent_domain', 'domain_id', 'user_id');
    }

    public function getIconUrlAttribute()
    {
        return $this->icon ? getImage($this->icon) : asset('assets/img/card.svg');
    }
}
