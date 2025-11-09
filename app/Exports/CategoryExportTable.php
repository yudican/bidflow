<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CategoryExportTable implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params, $title = null)
    {
        $this->params = $params;
    }

    public function query()
    {
        return Category::query();
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->slug,
            $row->status
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Slug',
            'Status',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER
        ];
    }

    // /**
    //  * @return array
    //  */
    // public function sheets(): array
    // {
    //     $sheets = [];

    //     return $sheets;
    // }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
