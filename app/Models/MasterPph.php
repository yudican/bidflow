<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPph extends Model
{
    use HasFactory;

    protected $fillable = ['pph_title', 'pph_percentage', 'pph_amount'];

    protected $dates = [];

    public $table = "master_pph";
}
