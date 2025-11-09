<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    use HasFactory;
    protected $table = 'addr_kabupaten';

    /**
     * Get all of the kecamatan for the Kabupaten
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kab_id', 'pid');
    }

    /**
     * Get all of the kelurahan for the Kecamatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agentAddressKabupaten()
    {
        return $this->hasMany(AgentAddress::class, 'kabupaten_id', 'pid')->leftJoin('agent_details', 'agent_address.user_id', '=', 'agent_details.user_id')->orderBy('order', 'asc');
    }
}
