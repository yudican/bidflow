<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Brand extends Model
{
    //use Uuid;
    use HasFactory;
    protected $appends = ['alamat', 'logo_url'];

    //public $incrementing = false;

    protected $guarded = [];
    protected $dates = [];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? getImage($this->logo) : asset('assets/img/card.svg');
    }

    // belongsTo Brand (one to one) model
    function products()
    {
        return $this->hasMany(Product::class);
    }
    // belongsTo Brand (one to one) model
    function banners()
    {
        return $this->belongsToMany(Banner::class, 'banner_brand');
    }

    // hasmany BrandCustomerSupport (one to many) model
    function brandCustomerSupport()
    {
        return $this->hasMany(BrandCustomerSupport::class, 'brand_id');
    }
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
        $alamat = "{$this->attributes['address']}, {$kel}, {$kec}, {$kab}, {$prov}  - {$kodepos}";

        return $alamat;
    }

    // many to many User (many to many) model
    function users()
    {
        return $this->belongsToMany(User::class, 'brand_user', 'brand_id', 'user_id');
    }

    /**
     * The masterPoints that belong to the Brand
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function masterPoints()
    {
        return $this->belongsToMany(MasterPoint::class, 'brand_master_point');
    }

    /**
     * The vouchers that belong to the Brand
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'brand_voucher');
    }

    /**
     * The vouchers that belong to the Brand
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function leadMasters()
    {
        return $this->belongsToMany(LeadMaster::class, 'brand_lead_master');
    }

    public function getPhoneAttribute($value)
    {
        return formatPhone($value, '+628');
    }
}
