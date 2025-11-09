<?php

namespace App\Exports;

use App\Models\InventoryProductReturn;
use App\Models\InventoryItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductReturnExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Product Return List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return InventoryItem::query()->where('type', 'return');
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->inventoryReturn->nomor_sr,
            $row->inventoryReturn->vendor,
            $row->inventoryReturn->vendor,
            $row->inventoryReturn->created_on,
            $row->inventoryReturn->created_by_name,
            $row->inventoryReturn->warehouse_name,
            $row->inventoryReturn->received_date,
            $row->inventoryReturn->transaction_channel,
            $row->inventoryReturn->case_title,
            $row->product_name,
            $row->sku,
            $row->u_of_m,
            $row->inventoryReturn->qty_pre_received,
            $row->inventoryReturn->qty_received,
            $row->inventoryReturn->note
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nomor Stock Return',
            'Vendor Code',
            'Vendor Name',
            'Created Date',
            'Created By',
            'Received Warehouse',
            'Received Date',
            'Transaction Channel',
            'Case Return',
            'Product',
            'SKU',
            'UOM',
            'QTY Diretur',
            'QTY Diterima',
            'Notes'
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
