<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VendorExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $product_convert;
    protected $title;
    public function __construct($product_convert, $title = 'ExportConverData')
    {
        $this->product_convert = $product_convert;
        $this->title = $title;
    }

    public function query()
    {
        return PurchaseOrder::groupBy('vendor_name');
    }

    public function map($row): array
    {
        $nameVendor = PurchaseOrder::groupBy('vendor_name')->get();

        $nominal = '';
        foreach($nameVendor as $nV){
            $nominal += $nv->subtotal;
        }
        return [
            $row->vendor_name,
            $row->vendor_code,
            $row->po_number,
            $row->type_po,
            $row->notes,
            $nominal,
            $row->tax
        ];
    }

    public function headings(): array
    {
        return [
            'Vendor Name',
            'Vendor Code',
            'PO Number',
            'Type PO',
            'Notes',
            'Subtotal',
            'Tax'
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
