<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterBin extends Model
{
    use HasFactory;
    protected $appends = ['alamat', 'stock', 'created_by_name'];
    protected $guarded = [];
    protected $dates = [];

    /**
     * The userWarehouse that belong to the Warehouse
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'master_bin_users');
    }

    /**
     * Get all of the masterBinStocks for the MasterBin
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function masterBinStocks()
    {
        return $this->hasMany(MasterBinStock::class);
    }

    public function getAlamatAttribute()
    {
        $provinsi =  DB::table('addr_provinsi')->where('pid', @$this->attributes['provinsi_id'])->first();
        $kabupaten =  DB::table('addr_kabupaten')->where('pid', @$this->attributes['kabupaten_id'])->first();
        $kecamatan =  DB::table('addr_kecamatan')->where('pid', @$this->attributes['kecamatan_id'])->first();
        $kelurahan =  DB::table('addr_kelurahan')->where('pid', @$this->attributes['kelurahan_id'])->first();

        // 
        $prov = $provinsi ? $provinsi->nama : '';
        $kab = $kabupaten ? $kabupaten->nama : '';
        $kec = $kecamatan ? $kecamatan->nama : '';
        $kel = $kelurahan ? $kelurahan->nama : '';
        $kodepos = $kelurahan ? $kelurahan->zip : @$this->attributes['kodepos'];
        $alamat = "{$this->attributes['address']}, {$kel}, {$kec}, {$kab}, {$prov} - {$kodepos}";

        return $alamat;
    }

    public function getStockAttribute()
    {
        return $this->masterBinStocks()->sum('stock');
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user?->name ?? '-';
    }
}
