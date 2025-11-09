<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactGroupAddressMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'contact_group_id',
        'nama',
        'telepon',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kelurahan_id',
        'kecamatan_id',
        'kodepos',
        'default'
    ];
}
