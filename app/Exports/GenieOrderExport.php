<?php

namespace App\Exports;

use App\Models\CommisionWithdraw;
use App\Models\OrderListByGenie;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class GenieOrderExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $title;
    public function __construct($title = 'ExportConverData')
    {
        $this->title = $title;
    }

    public function query()
    {
        return CommisionWithdraw::query();
    }

    public function map($row): array
    {
        return [
            $row->created_at,
            $row->user_name,
            $row->email,
            $row->phone,
            $row->request_by_name,
            $row->created_at,
            $row->amount,
            $row->status,
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Nama',
            'Email',
            'No. Handphone',
            'Request By',
            'Tanggal Pengajuan',
            'Nominal',
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
