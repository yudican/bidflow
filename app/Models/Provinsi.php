<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;
    protected $table = 'addr_provinsi';

    /**
     * Get all of the kabupaten for the Provinsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kabupaten()
    {
        return $this->hasMany(Kabupaten::class, 'prov_id', 'pid');
    }
}
