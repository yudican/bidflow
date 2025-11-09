<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReceivingExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $product_convert;
    protected $title;
    public function __construct($product_convert, $title = 'ExportReceiving')
    {
        $this->product_convert = $product_convert;
        $this->title = $title;
    }

    public function query()
    {
        return PurchaseOrderItem::with('purchaseOrder')->groupBy('receiving_number');
    }

    public function map($row): array
    {
        return [
            $row->purchaseOrder->vendor_name,
            $row->invoice_date,
            $row->vendor_doc_number,
            $row->subtotal_qty_diterima,
            $row->due_date,
            $row->subtotal,
            '-'
        ];
    }

    public function headings(): array
    {
        return [
            'Vendor Name',
            'Date Invoicing',
            'Invoicing Number',
            'Total Invoicing',
            'Date Payment',
            'Total Pembayaran',
            'Kurang - Lebih Pembayaran'
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
