<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ContactGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'address',
        'deskripsi',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['created_by_name', 'updated_by_name'];

    /**
     * Get all of the groupMembers for the ContactGroup
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMembers(): HasMany
    {
        return $this->hasMany(ContactGroupMember::class, 'contact_group_id');
    }


    /**
     * Get all of the logs for the ContactGroupMember
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(LogAction::class, 'model_id');
    }

    /**
     * Get all of the comments for the ContactGroup
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupAddressMembers(): HasMany
    {
        return $this->hasMany(ContactGroupAddressMember::class, 'contact_group_id');
    }

    /**
     * Get all of the DOH settings for the ContactGroup
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dohSettings(): HasMany
    {
        return $this->hasMany(ContactGroupDohSetting::class, 'contact_group_id');
    }

    public function getCreatedByNameAttribute()
    {
        $user = DB::table('users')->where('id', $this->created_by)->select('name')->first();

        return $user ? $user->name : '-';
    }

    public function getUpdatedByNameAttribute()
    {
        $user = DB::table('users')->where('id', $this->updated_by)->select('name')->first();

        return $user ? $user->name : '-';
    }
}
