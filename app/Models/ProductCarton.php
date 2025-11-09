<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCarton extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['vendor', 'package'];

    public function getVendorAttribute()
    {
        $vendor = Vendor::where('vendor_code', $this->vendor_id)->first();
        return $vendor?->name ?? '-';
    }

    public function getPackageAttribute()
    {
        $package = Package::find($this->moq);
        return $package?->name ?? '-';
    }
}
