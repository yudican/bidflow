<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseOrderExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Purchase Order List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return PurchaseOrder::query();
    }

    public function map($row): array
    {
        foreach ($row->items as $item) {
            return [
                $row->id,
                $row->po_number,
                $row->vendor_code,
                $row->vendor_name,
                $row->status,
                $row->type_po,
                $row->channel,
                $row->created_by_name,
                $row->created_at,
                $row->getPaymentTermNameAttribute(),
                $row->notes,
                @$item->product_name,
                @$item->qty,
                @$item->uom,
                @$item->price,
                (empty($row->total_tax)?'0':$row->total_tax),
                @$item->subtotal,
                @$item->total_amount,
            ];
        }
        
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nomor PO',
            'Vendor Code',
            'Vendor Name',
            'Status',
            'Type PO',
            'Channel List',
            'Created By',
            'Created On',
            'Payment Term',
            'Notes',
            'Product',
            'Qty',
            'UOM',
            'Harga',
            'Total Tax',
            'Sub Total',
            'Total'
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
