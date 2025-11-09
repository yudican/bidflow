<?php

namespace App\Exports;

use App\Models\InventoryItem;
use App\Models\InventoryProductStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductKonsinyasiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Product Konsinyasi List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return InventoryProductStock::query()->where('inventory_type', 'konsinyasi')
            ->whereHas('orderTransfer');
    }

    public function map($row): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        return [
            $rowNumber,
            $row->orderTransfer->transfer_number,
            $row->orderTransfer->order_number,
            $row->orderTransfer->preference_number,
            $row->orderTransfer->userContact->name,
            $row->orderTransfer->userContact->uid,
            $row->warehouse_name,
            $row->bin_destination_name,
            $row->allocated_by_name,
            $row->orderTransfer->created_at,
            $row->status
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'TRF ID',
            'SO Number',
            'No. Preferences',
            'Contact',
            'Customer Code',
            'Warehouse',
            'Destination Warehouse',
            'Alokasi By',
            'Created On',
            'Status'
        ];
    }
}

