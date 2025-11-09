<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqSubmenu extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['sub_menu', 'is_like', 'is_comment', 'status'];

    protected $dates = [];

    protected $table = 'faq_sub_menus';

    // hasMany FaqContent (faq_contents) -> submenu_id
    public function faqContents()
    {
        return $this->hasMany(FaqContent::class, 'submenu_id');
    }
}
