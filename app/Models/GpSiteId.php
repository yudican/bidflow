<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpSiteId extends Model
{
    use HasFactory;

    protected $table = 'gp_site_id';

    protected $fillable = ['site_id', 'status'];
    protected $appends = ['warehouse_name'];

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::where('wh_id', $this->site_id)->first('name');
        if ($warehouse) {
            return $warehouse->name;
        }

        return '-';
    }
}
