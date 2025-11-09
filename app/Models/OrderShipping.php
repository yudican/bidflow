<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderShipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid_lead',
        'sender_name',
        'sender_phone',
        'resi',
        'expedition_name',
        'order_type',
        'cretated_by',
        'attachment',
        'delivery_date'
    ];

    protected $appends = ['created_name', 'attachments', 'attachment_url'];

    public function getCreatedNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user ? $user->name : '-';
    }

    public function order()
    {
        if ($this->order_type == 'lead') {
            return $this->belongsTo(OrderLead::class, 'uid_lead', 'uid_lead');
        }
        return $this->belongsTo(OrderManual::class, 'uid_lead', 'uid_lead');
    }

    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            $attachments = explode(',', $this->attachment);
            $attachments = array_map(function ($attachment) {
                return getImage($attachment);
            }, $attachments);

            return $attachments;
        }

        return [];
    }

    public function getAttachmentsAttribute()
    {
        if ($this->attachment) {
            $attachments = explode(',', $this->attachment);
            return $attachments;
        }

        return [];
    }
}
