<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqContent extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['submenu_id', 'category_id', 'title', 'content', 'image', 'video', 'status'];

    protected $dates = [];


    /**
     * Get all of the faqLikes for the FaqSubmenu
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faqLikes()
    {
        return $this->hasMany(FaqLike::class, 'content_id');
    }
}
