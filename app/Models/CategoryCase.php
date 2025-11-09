<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryCase extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['type_id', 'category_name', 'notes'];

    protected $dates = [];

    protected $appends = ['type_case_name'];

    public function typeCase()
    {
        return $this->belongsTo(TypeCase::class, 'type_id');
    }

    public function getTypeCaseNameAttribute()
    {
        return $this->typeCase?->type_name;
    }
}
