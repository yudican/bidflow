<?php

namespace App\Exports;

use App\Models\LeadMaster;
use App\Models\Brand;
use App\Models\InventoryItem;
use App\Models\LeadActivity;
use App\Models\LeadHistory;
use App\Models\LeadNegotiation;
use App\Models\OrderManual;
use App\Models\SalesReturn;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReturnDetailExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $product_convert;
    protected $title;
    public function __construct($product_convert, $title = 'ExportSalesReturn')
    {
        $this->product_convert = $product_convert;
        $this->title = $title;
    }

    public function query()
    {
        $items = [
            'contactUser',
            'salesUser',
            'addressUser',
            'createUser',
            'courierUser',
            'brand',
            'leadActivities',
            'user',
            'warehouse',
            'paymentTerm',
            'returnItems'
        ];
        return SalesReturn::with($items);
    }

    public function map($row): array
    {
        return [
            $row->sr_number,
            $row->order_number,
            $row->contactUser ? $row->contactUser->name : '-',
            $row->salesUser ? $row->salesUser->name : '-',
            $row->status,
            $row->created_at,
            $row->payment_term,
        ];
    }

    public function headings(): array
    {
        return [
            'SR Number',
            'Order Number',
            'Contact',
            'Sales',
            'Status',
            'Created On',
            'Payment Term',
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
