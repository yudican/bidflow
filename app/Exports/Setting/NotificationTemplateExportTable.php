<?php

namespace App\Exports\Setting;

use App\Models\NotificationTemplate as ModelsNotificationTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NotificationTemplateExportTable implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $group_id;
    public function __construct($group_id)
    {
        $this->group_id = $group_id;
    }

    public function query()
    {
        return ModelsNotificationTemplate::query()
            ->where('group_id', $this->group_id)
            ->with('roles');
    }

    public function map($row): array
    {
        return [
            $row->notification_code,
            $row->notification_title,
            $row->notification_subtitle,
            $this->getNotificationType($row->notification_type),
            $row->roles()->pluck('role_name')->implode(', '),
            // remove html tags &nbsp; replace with new line
            str_replace('&nbsp;', '', strip_tags($row->notification_body)),
        ];
    }

    public function headings(): array
    {
        return [
            'Code',
            'Title',
            'Subtitle',
            'Type',
            'Role',
            'Body',
        ];
    }

    // get notification type
    public function getNotificationType($type)
    {
        if ($type == 'alert') {
            return 'Alert';
        } else if ($type == 'email') {
            return 'Email';
        } else {
            return 'Email & Alert';
        }
    }
}
