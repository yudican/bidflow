<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    //use Uuid;
    use HasFactory;
    protected $appends = ['alamat', 'stock'];
    protected $guarded = [];
    protected $dates = [];

    /**
     * The userWarehouse that belong to the Warehouse
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'warehouse_users');
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
        $alamat = "{$this->address}, {$kel}, {$kec}, {$kab}, {$prov} - {$kodepos}";

        return $alamat;
    }

    public function getStockAttribute()
    {
        $company_account = CompanyAccount::whereStatus(1)->first();
        $stock = ProductStock::where('warehouse_id', $this->id)->where('company_id', $company_account->id)->sum('stock');
        return $stock;
    }
}
