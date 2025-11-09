<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterOngkir extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'nama_ogkir',
        'kode_ongkir',
        'harga_ongkir',
        'status_ongkir',
        'start_date',
        'end_date'
    ];
    protected $table = 'master_ongkir';

    protected $with = ['logistic'];

    public function logistic()
    {
        return $this->belongsToMany(Logistic::class, 'ongkir_logistic', 'master_ongkir_id', 'logistic_id');
    }
}
