<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = [
        'menu_label',
        'menu_route',
        'menu_icon',
        'badge',
        'show_menu',
        'menu_order',
        'parent_id',
    ];
    /**
     * The roles that belong to the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the parent that owns the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id')->where('parent_id', null)->with('parent');
    }

    /**
     * Get all of the children for the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->with('children')->orderBy('menu_order', 'ASC');
    }
}
