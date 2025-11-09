<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['contact', 'uid_case', 'title', 'type_id', 'category_id', 'priority_id', 'source_id', 'status_id', 'description', 'created_by', 'updated_by', 'status_approval', 'approval_notes'];

    protected $dates = [];

    protected $table = 'case_masters';

    protected $appends = ['type_name', 'category_name', 'priority_name', 'source_name', 'status_name', 'created_by_name', 'updated_by_name', 'contact_name'];

    /**
     * Get all of the items for the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(CaseItem::class, 'uid_case', 'uid_case');
    }

    /**
     * Get the contact that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contactUser()
    {
        return $this->belongsTo(User::class, 'contact');
    }

    /**
     * Get the contact that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the type that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function typeCase()
    {
        return $this->belongsTo(TypeCase::class, 'type_id');
    }

    /**
     * Get the type that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priorityCase()
    {
        return $this->belongsTo(PriorityCase::class, 'priority_id');
    }

    /**
     * Get the type that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sourceCase()
    {
        return $this->belongsTo(SourceCase::class, 'source_id');
    }

    /**
     * Get the type that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoryCase()
    {
        return $this->belongsTo(CategoryCase::class, 'category_id');
    }

    /**
     * Get the statusCase that owns the Cases
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statusCase()
    {
        return $this->belongsTo(StatusCase::class, 'status_id');
    }

    public function getTypeNameAttribute()
    {
        $type = TypeCase::find($this->type_id);

        return $type ? $type->type_name : '-';
    }

    public function getCategoryNameAttribute()
    {
        $category = CategoryCase::find($this->category_id);

        return $category ? $category->category_name : '-';
    }

    public function getPriorityNameAttribute()
    {
        $priority = PriorityCase::find($this->priority_id);

        return $priority ? $priority->priority_name : '-';
    }

    public function getSourceNameAttribute()
    {
        $source = SourceCase::find($this->source_id);

        return $source ? $source->source_name : '-';
    }

    public function getStatusNameAttribute()
    {
        $status = StatusCase::find($this->status_id);

        return $status ? $status->status_name : '-';
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user ? $user->name : '-';
    }

    public function getUpdatedByNameAttribute()
    {
        $user = User::find($this->updated_by);
        return $user ? $user->name : '-';
    }

    public function getContactNameAttribute()
    {
        $user = User::find($this->contact);
        return $user ? $user->name : '-';
    }
}
