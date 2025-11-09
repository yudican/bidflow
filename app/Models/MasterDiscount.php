<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDiscount extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['title', 'percentage', 'sales_channel', 'sales_tag'];

    protected $dates = [];

    protected $appends = ['sales_channels'];

    public function getSalesChannelsAttribute()
    {
        $sales_channels = $this->sales_channel;
        if ($sales_channels) {
            $sales_channels = explode(',', $sales_channels);

            return $sales_channels;
        }

        return [];
    }
}
