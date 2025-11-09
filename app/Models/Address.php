<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['type','nama','alamat','provinsi_id','kabupaten_id','kecamatan_id','kelurahan_id','kodepos','telepon','catatan','user_id'];
    
    protected $dates = [];
}
