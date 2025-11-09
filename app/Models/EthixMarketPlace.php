<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EthixMarketPlace extends Model
{
    use HasFactory;

    /**
     * Get all of the items for the EthixMarketPlace
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(EthixMarketPlaceItem::class);
    }
}
