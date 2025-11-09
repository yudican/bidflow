<?php

namespace App\Exports;

use App\Models\OrderLead;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrderLeadDetailExport implements FromView, ShouldAutoSize
{
    protected $uid_lead;
    protected $title;
    public function __construct($uid_lead, $title = 'ExportOrderLeadDetail')
    {
        $this->uid_lead = $uid_lead;
        $this->title = $title;
    }

    public function view(): View
    {
        $leads = OrderLead::whereHas('productNeeds');
        if (is_array($this->uid_lead)) {
            $leads->whereIn('uid_lead', $this->uid_lead);
        }
        $new_leads = $leads->get();
        $lead_data = [];

        foreach ($new_leads as $key => $value) {
            // merge value same value
            $lead_data[$key]['title']       = $value->title;
            $lead_data[$key]['contact']     = $value?->contactUser?->name;
            $lead_data[$key]['company']     = $value?->contactUser?->company?->name ?? '-';
            $lead_data[$key]['customer_need'] = $value->customer_need;
            $lead_data[$key]['pic_sales']   = $value?->salesUser?->name;
            $lead_data[$key]['created_on']  = $value->created_at;
            $lead_data[$key]['created_by']  = $value?->createUser?->name;
            $lead_data[$key]['warehouse']   = $value?->courierUser?->name;
            $lead_data[$key]['order_number'] = $value->order_number;
            $lead_data[$key]['invoice_number'] = $value->invoice_number;
            $lead_data[$key]['payment_term'] = $value?->paymentTerm?->name;
            $lead_data[$key]['due_date']    = $value->due_date;
            $lead_data[$key]['address_type'] = $value->type;
            $lead_data[$key]['address_name'] = $value->nama;
            $lead_data[$key]['address_telp'] = $value->telepon;
            $lead_data[$key]['address_street'] = $value->alamat_detail;
            $lead_data[$key]['tipe_pengiriman'] = 'Normal';
            $lead_data[$key]['notes'] = $value->notes;
            $lead_data[$key]['tipe_ekspedisi'] = $value->shipping_type;
            $lead_data[$key]['ongkir'] = $value->ongkir;
            $lead_data[$key]['product'] = $value->productNeeds()->get()->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'price' => $item->prices['final_price'],
                    'qty' => $item->qty,
                    'tax_amount' => $item->tax_amount,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                    'price_nego' => $item->price_nego,
                    'total_price' => $item->total,
                ];
            });
        }
        return view('export.lead-order-manual', [
            'data' => $lead_data,
        ]);
    }

    // public function query()
    // {
    //     return OrderLead::with([
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
    //         $row?->contactUser?->name,
    //         $row?->contactUser?->company?->name,
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
