<?php

namespace App\Exports;

use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\User;
use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactSkuExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
        return User::with(['company', 'brand']);
    }

    public function map($row): array
    {

        return [
            $row->name,
            $row->email,
            $row->telepon,
            $row->gender,
            $row->bod,
            $row->brand ? $row->brand->name : '-',
            $row->company_name ? $row->company_name : '-',
            $row->company ? $row->company->email : '-',
            $row->company ? $row->company->phone : '-',
            $row->company ? $row->company->npwp_name : '-',
            $row->company ? $row->company->npwp : '-',
            $row->company ? $row->company->owner_name : '-',
            $row->company ? $row->company->owner_phone : '-',
            $row->company ? $row->company->business_entity : '-',
            $row->company ? $row->company->pic_name : '-',
            $row->company ? $row->company->pic_phone : '-',
            $row->company ? $row->company->address : '-',
            $row->amount_detail['total_debt'],
            $row->deposit,
            $row->amount_detail['total_invoice'],
            $row->amount_detail['total_amount'],
            $row->sales_channel,
            $row->role->role_name,
            $row->created_by_name,
            date('d-m-Y', strtotime($row->created_at)),
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Telepon',
            'Gender',
            'BOD',
            'Brand',
            'Company Name',
            'Company Email',
            'Company Telepon',
            'Nama NPWP',
            'No NPWP',
            'Owner Name',
            'Owner Telepon',
            'Business Entity',
            'PIC Name',
            'PIC Telepon',
            'Company Address',
            'Total Debt',
            'Deposito',
            'Invoice Active',
            'Total Amount',
            'Sales Tag',
            'Role',
            'Created By Name',
            'Created Date',
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
