<?php

namespace App\Exports;

use App\Models\OrderLead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderLeadExportTable implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Order Lead List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return OrderLead::query();
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->title,
            $row->contact,
            $row->sales,
            $row->created_by,
            $row->payment_term,
            $row->status,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Contact',
            'Sales',
            'Created By',
            'Payment Term',
            'Status',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
