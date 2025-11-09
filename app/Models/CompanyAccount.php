<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompanyAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_code',
        'account_name',
        'account_phone',
        'account_email',
        'account_address',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'kodepos',
        'account_logo',
        'account_description',
        'status'
    ];

    protected $appends = [
        'alamat',
        'account_logo_url'
    ];

    public function getAlamatAttribute()
    {
        $provinsi =  DB::table('addr_provinsi')->where('pid', $this->attributes['provinsi_id'])->first();
        $kabupaten =  DB::table('addr_kabupaten')->where('pid', $this->attributes['kabupaten_id'])->first();
        $kecamatan =  DB::table('addr_kecamatan')->where('pid', $this->attributes['kecamatan_id'])->first();
        $kelurahan =  DB::table('addr_kelurahan')->where('pid', $this->attributes['kelurahan_id'])->first();

        // 
        $prov = $provinsi ? $provinsi->nama : '';
        $kab = $kabupaten ? $kabupaten->nama : '';
        $kec = $kecamatan ? $kecamatan->nama : '';
        $kel = $kelurahan ? $kelurahan->nama : '';
        $kodepos = $kelurahan ? $kelurahan->zip : $this->attributes['kodepos'];
        $alamat = "{$this->attributes['account_address']}, {$kel}, {$kec}, {$kab}, {$prov}  - {$kodepos}";

        return $alamat;
    }

    public function getAccountLogoUrlAttribute()
    {
        return $this->account_logo ? getImage($this->account_logo) : asset('images/no-image.png');
    }

    public function getAccountPhoneAttribute($value)
    {
        return formatPhone($value, '+628');
    }
}
