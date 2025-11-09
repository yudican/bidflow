<?php

namespace App\Exports;

use App\Models\OrderManual;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrderManualDetailExport implements FromView, ShouldAutoSize
{
    protected $uid_lead;
    protected $title;
    public function __construct($uid_lead, $title = 'ExportConverData')
    {
        $this->uid_lead = $uid_lead;
        $this->title = $title;
    }

    public function view(): View
    {
        $account_id = auth()->user()->account_id;
        $leads = OrderManual::where('company_id', $account_id)->whereHas('productNeeds');

        $leads->where('uid_lead', $this->uid_lead);

        $new_leads = $leads->get();
        $lead_data = [];

        foreach ($new_leads as $key => $value) {
            $status = match ($value->status) {
                1 => 'New',
                2 => 'Open',
                3 => 'Close',
                4 => 'Cancel',
                default => '-',
            };
            // Convert created_at to yyyy-mm-dd
            $created_at = $value->created_at->format('Y-m-d');
            $lead_data[$key] = [
                'title' => $value->title,
                'contact' => $value?->contactUser?->name,
                'company' => $value?->contactUser?->company?->name ?? '-',
                'customer_need' => $value->customer_need,
                'pic_sales' => $value?->salesUser?->name,
                'created_on' => $created_at,
                'created_by' => $value?->createUser?->name,
                'warehouse' => $value?->courierUser?->name,
                'order_number' => $value->order_number,
                'invoice_number' => $value->invoice_number,
                'payment_term' => $value?->paymentTerm?->name,
                'expired_date' => $value?->expired_date,
                'due_date' => $value->due_date,
                'type' => $value->type,
                'address_type' => $value?->addressUser?->type,
                'address_name' => $value?->addressUser?->nama,
                'address_telp' => $value?->addressUser?->telepon,
                'address_street' => $value?->addressUser?->alamat_detail,
                'tipe_pengiriman' => 'Normal',
                'notes' => $value->notes,
                'status' => $status,
                'print_status' => $value->print_status,
                'resi_status' => $value->resi_status,
                'product' => $value->productNeeds()->get()->map(function ($item) {
                    $dpp = $item->price - $item->discount_amount;
                    $ppn = $dpp * $item->tax_percentage;
                    return [
                        'sku' => @$item->product?->sku,
                        'product_name' => @$item->product_name,
                        'price' => $item->price,
                        'qty' => $item->qty,
                        'tax_amount' => $ppn,
                        'discount_amount' => $item->discount_amount,
                        'subtotal' => $item->subtotal,
                        'price_nego' => $dpp + $ppn,
                        'total_price' => $item->total,
                    ];
                }),
            ];
        }
        return view('export.lead-order-manual', [
            'data' => $lead_data,
            'type' => 'manual'
        ]);
    }

    // public function query()
    // {
    //     return OrderManual::with([
    //         'billings',
    //         'reminders',
    //         'contactUser',
    //         'salesUser',
    //         'addressUser',
    //         'createUser',
    //         'courierUser',
    //         'brand',
    //         'leadActivities',
    //         'negotiations',
    //         'warehouse',
    //         'paymentTerm',
    //         'productNeeds',
    //         'productNeeds.product',
    //         'contactUser.company',
    //         'contactUser.addressUsers',
    //         'orderShipping'
    //     ])->where('uid_lead', $this->uid_lead);
    // }

    // public function map($row): array
    // {
    //     return [
    //         $row->title,
    //         $row?->contactUser?->name ?? '-',
    //         $row?->contactUser?->company?->name ?? '-',
    //         $row->customer_need,
    //         $row?->salesUser?->name,
    //         $row->created_at,
    //         $row?->createUser?->name,
    //         $row?->courierUser?->name,
    //         $row->order_number,
    //         $row->invoice_number,
    //         $row?->paymentTerm?->name,
    //         $row->due_date,
    //         $row->type,
    //         $row->nama,
    //         $row->telepon,
    //         $row->alamat_detail,
    //         'Normal',
    //         $row?->productNeeds?->product?->name,
    //         $row?->productNeeds?->price,
    //         $row?->productNeeds?->price,
    //         $row?->ProductNeed?->tax?->tax_code,
    //         $row?->ProductNeed?->discount?->title,
    //         $row?->ProductNeed?->price,
    //         $row->kode_unik,
    //         $row->ongkir,
    //         $row->price,
    //         $row->notes,
    //     ];
    // }

    // public function headings(): array
    // {
    //     return [
    //         'Title',
    //         'Contact',
    //         'Company',
    //         'Customer Need',
    //         'PIC Sales',
    //         'Created On',
    //         'Created By',
    //         'Warehouse',
    //         'Order Number',
    //         'Invoice Number',
    //         'Payment Term',
    //         'Due Date',
    //         'Addres type',
    //         'Address name',
    //         'Address Telp',
    //         'Address Street',
    //         'Tipe Pengiriman',
    //         'Product',
    //         'Qty',
    //         'Normal Price',
    //         'Nego Price',
    //         'Tax',
    //         'Discount',
    //         'Subtotal',
    //         'Kode Unik',
    //         'Ongkir',
    //         'Total Price',
    //         'Notes',
    //     ];
    // }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
