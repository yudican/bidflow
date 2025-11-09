<?php

namespace App\Exports;

use App\Models\Menu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MenuExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Menu List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return Menu::query();
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->menu_label,
            $row->menu_route,
            $row->menu_icon,
            $row->menu_order,
            $row->show_menu,
        ];
    }

    public function headings(): array
    {
        return [
            'ID Menu',
            'Nama Menu',
            'Route Menu',
            'Icon',
            'Urutan Menu',
            'Tampilkan Menu',
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
