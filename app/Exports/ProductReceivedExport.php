<?php

namespace App\Exports;

use App\Models\InventoryProductStock;
use App\Models\InventoryItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductReceivedExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Product Received List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return InventoryItem::query()->whereHas('inventoryStock', function ($query) {
            return $query->where('inventory_type', 'received');
        });
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->inventoryStock->reference_number,
            $row->inventoryStock->vendor,
            $row->inventoryStock->received_by_name,
            $row->inventoryStock->received_date,
            $row->inventoryStock->status,
            $row->inventoryStock->warehouse->name,
            $row->inventoryStock->company_name,
            $row->product_name,
            $row->getSkuAttribute(),
            $row->getUOfMAttribute(),
            $row->qty
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'PO Number',
            'Vendor Code',
            'Received By',
            'Received Date',
            'Status',
            'Received WH',
            'Company',
            'Product',
            'SKU',
            'UOM',
            'QTY Diterima'
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
