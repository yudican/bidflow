<?php

namespace App\Exports;

use App\Models\OrderLead;
use App\Models\PurchaseInvoiceEntry;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoiceEntryExport implements FromView, ShouldAutoSize
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
        $status = $request->status;
        $created_at = $request->tanggal;
        $order =  PurchaseInvoiceEntry::query();

        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('po_number', 'like', "%$search%");
                $query->orWhere('vendor_code', 'like', "%$search%");
                $query->orWhereHas('createdBy', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            });
        }

        if ($status) {
            $order->whereIn('status', $status);
        }

        if ($created_at) {
            $order->whereBetween('created_at', $created_at);
        }


        $orders = $order->orderBy('status', 'asc')->orderBy('created_at', 'desc')->paginate($request->perpage);
        $datas = [];

        foreach ($orders as $key => $value) {
            // merge value same value
            $datas[$key]['received_number'] = $value->received_number;
            $datas[$key]['doc_number']      = $value?->doc_number;
            $datas[$key]['invoice_date']    = $value?->invoice_date ?? '-';
            $datas[$key]['created_by']      = $value->created_by_name;
            $datas[$key]['vendor']          = $value?->vendor_name;
            $datas[$key]['type_invoice']    = $value->type_invoice;
            $datas[$key]['status']          = $value?->status_name;
            $datas[$key]['product']         = $value->items()->get()->map(function ($item) {
                return [
                    'po_number' => $item?->po_number ?? '-',
                    'received_number' => $item?->received_number ?? '-',
                    'product_name' => $item?->product_name,
                    'extended_cost' => $item?->extended_cost,
                    'qty' => $item?->qty,
                    'sku' => $item?->sku,
                    'ppn' => $item?->ppn,
                    'subtotal' => $item?->subtotal,
                ];
            });
        }
        return view('export.invoice_entry', [
            'data' => $datas,
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
