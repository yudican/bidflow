<?php

namespace App\Exports;

use App\Models\OrderLead;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrderLeadExport implements FromView, ShouldAutoSize
{
    protected $request;
    protected $title;
    public function __construct($request, $title = 'ExportConverData')
    {
        $this->request = $request;
        $this->title = $title;
    }

    public function view(): View
    {
        $request = $this->request;
        $search = $request->search;
        $contact = $request->contact;
        $sales = $request->sales;
        $created_at = $request->created_at;
        $status = $request->status;
        $user = auth()->user();
        $role = $user->role->role_type;
        $account_id = $request->account_id;
        $payment_term = $request->payment_term;
        $print_status = $request->print_status;
        $resi_status = $request->resi_status;
        $orderLead =  OrderLead::query()->whereHas('productNeeds');
        if ($search) {
            $orderLead->where('order_number', 'like', "%$search%");
            $orderLead->orWhereHas('contactUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $orderLead->orWhereHas('salesUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $orderLead->orWhereHas('createUser', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
        }
        if ($contact) {
            $orderLead->whereIn('contact', $contact);
        }

        if ($sales) {
            $orderLead->whereIn('sales', $sales);
        }

        if ($status) {
            $orderLead->whereIn('status', $status);
        }

        if ($created_at) {
            $orderLead->whereBetween('created_at', $created_at);
        }

        if ($payment_term) {
            $orderLead->whereIn('payment_term', $payment_term);
        }

        // cek switch account
        if ($account_id) {
            $orderLead->where('company_id', $account_id);
        }

        if ($print_status) {
            $orderLead->where('print_status', $print_status);
        }

        if ($resi_status) {
            $orderLead->where('resi_status', $resi_status);
        }

        if ($role == 'sales') {
            $orderLead->where('user_created', $user->id)->orWhere('sales', $user->id);
        }


        $new_leads = $orderLead->orderBy('created_at', 'desc')->get();
        $lead_data = [];

        foreach ($new_leads as $key => $value) {

            if ($value->status == 1) {
                $status = 'New';
            } elseif ($value->status == 2){
                $status = 'Open';
            } elseif ($value->status == 3){
                $status = 'Close';
            } elseif ($value->status == 4){
                $status = 'Cancel';
            } else {
                $status = '-';
            }

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
            $lead_data[$key]['status'] = $status ;
            $lead_data[$key]['print_status'] = $value->print_status;
            $lead_data[$key]['resi_status'] = $value->resi_status;
            $lead_data[$key]['product'] = $value->productNeeds()->get()->map(function ($item) {
                return [
                    'sku' => @$item->product?->sku,
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


    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
