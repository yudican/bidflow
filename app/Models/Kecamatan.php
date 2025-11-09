<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kecamatan extends Model
{
    use HasFactory;
    protected $table = 'addr_kecamatan';
    protected $appends = ['nama_kabupaten', 'nama_provinsi', 'result', 'result_id', 'kodepos'];

    /**
     * Get all of the kelurahan for the Kecamatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agentAddress()
    {
        return $this->hasMany(AgentAddress::class, 'kecamatan_id', 'pid');
    }

    public function getNamaKabupatenAttribute()
    {
        $kabupaten = Kabupaten::where('pid', $this->kab_id)->first();
        return $kabupaten ? $kabupaten->nama : '-';
    }

    public function getNamaProvinsiAttribute()
    {
        $provinsi = Provinsi::where('pid', $this->prov_id)->first();
        return $provinsi ? $provinsi->nama : '-';
    }

    public function getResultAttribute()
    {
        return $this->nama . '/' . $this->nama_kabupaten . '/' . $this->nama_provinsi;
    }

    public function getResultIdAttribute()
    {
        $kelurahan = DB::table('addr_kelurahan')->where('kec_id', $this->pid)->first();
        $kabupaten = DB::table('addr_kabupaten')->where('pid', $this->kab_id)->first();
        $provinsi = DB::table('addr_provinsi')->where('pid', $this->prov_id)->first();
        $kelurahan_id = $kelurahan ? $kelurahan->pid : 0;
        $kabupaten_id = $kabupaten ? $kabupaten->pid : 0;
        $provinsi_id = $provinsi ? $provinsi->pid : 0;
        $kodepos = $kelurahan ? $kelurahan->zip : 0;
        $kecamatan_id = $this->pid ?? 0;
        // format: kelurahan_id/kecamatan_id/kabupaten_id/provinsi_id/kodepos

        return "{$kelurahan_id}/{$kecamatan_id}/{$kabupaten_id}/{$provinsi_id}/{$kodepos}";
    }

    public function getKodePosAttribute()
    {
        $kelurahan = DB::table('addr_kelurahan')->where('kec_id', $this->pid)->first();
        $kodepos = $kelurahan ? $kelurahan->zip : 0;

        return $kodepos;
    }
}
