<?php

namespace App\Exports;

use App\Models\OrderManual;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Pusher\Pusher;
use App\Models\User;

class OrderManualExport implements FromView, ShouldAutoSize, ShouldQueue
{
    use Queueable;
    public $timeout = 300;
    public $tries = 3;
    protected $requestData;
    protected $title;

    public function __construct($request, $title = 'ExportConverData')
    {
        $this->requestData = [
            'type' => $request->type,
            'search' => $request->search,
            'contact' => $request->contact,
            'sales' => $request->sales,
            'created_at' => $request->created_at,
            'status' => $request->status,
            'account_id' => $request->account_id,
            'payment_term' => $request->payment_term,
            'print_status' => $request->print_status,
            'resi_status' => $request->resi_status,
            'user_id' => $request->user_id
        ];

        $this->title = $title;
    }

    public function view(): View
    {
        $pusher = new Pusher(
            'f01866680101044abb79',
            '4327409f9d87bdc35960',
            '1887006',
            [
                'cluster' => 'ap1',
                'useTLS' => true
            ]
        );

        $type = $this->requestData['type'];
        $key_export  = 'export-so-' . $type . '-' . $this->requestData['user_id'];

        $pusher->trigger($key_export, 'progress', [
            'message' => 'Memulai export data...',
            'progress' => 0
        ]);

        $search = $this->requestData['search'];
        $contact = $this->requestData['contact'];
        $sales = $this->requestData['sales'];
        $created_at = $this->requestData['created_at'];
        $status = $this->requestData['status'];
        $user = User::find($this->requestData['user_id']);
        $role = $user->role->role_type;
        $account_id = $this->requestData['account_id'];
        $payment_term = $this->requestData['payment_term'];
        $print_status = $this->requestData['print_status'];
        $resi_status = $this->requestData['resi_status'];

        $orderLead = OrderManual::query()->whereHas('productNeeds');

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

        if ($type) {
            $orderLead->where('type', $type);
        }

        if ($sales) {
            $orderLead->whereIn('sales', $sales);
        }

        if ($status) {
            $orderLead->whereIn('status', $status);
        }

        if ($created_at) {
            $startDate = $created_at[0];
            $endDate = $created_at[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $orderLead->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($payment_term) {
            $orderLead->whereIn('payment_term', $payment_term);
        }

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
        $total = $new_leads->count();
        $lead_data = [];

        $pusher->trigger($key_export, 'progress', [
            'message' => "Memproses $total data...",
            'progress' => 10
        ]);

        $status = '-';
        foreach ($new_leads as $key => $value) {
            switch ($value->status) {
                case '1':
                    $status = 'New';
                    break;
                case '2':
                    $status = 'Open';
                    break;
                case '3':
                    $status = 'Close';
                    break;
                case '4':
                    $status = 'Cancel';
                    break;
                case -1:
                    $status = 'Draft';
                    break;
                default:
                    $status = '-';
                    break;
            }

            // Convert created_at to yyyy-mm-dd
            $created_at = $value->created_at->format('Y-m-d');

            // Merge value same value
            $lead_data[$key] = [
                'contact' => $value?->contactUser?->name,
                'company' => $value?->contactUser?->company?->name ?? '-',
                'customer_need' => $value->customer_need,
                'pic_sales' => $value?->salesUser?->name,
                'created_on' => $created_at,
                'created_by' => $value?->createUser?->name,
                'warehouse' => $value?->warehouse?->name,
                'order_number' => $value->order_number,
                'invoice_number' => $value->invoice_number,
                'payment_term' => $value?->paymentTerm?->name,
                'expired_date' => Carbon::parse($value->expired_at)->format('d-m-Y'),
                'address_type' => $value?->addressUser?->type,
                'address_name' => $value?->addressUser?->nama,
                'address_telp' => $value?->addressUser?->telepon,
                'address_street' => $value?->addressUser?->alamat_detail,
                'tipe_pengiriman' => 'Normal',
                'notes' => $value->notes,
                'status' => $status,
                'ongkir' => $value->ongkir,
                'print_status' => strtoupper($value->print_status),
                'resi_status' => strtoupper($value->resi_status),
                'product' => $this->mapProducts($value->productNeeds()->get()),
            ];

            if ($key % ceil($total / 10) == 0) {
                $progress = ceil(($key / $total) * 100);
                $pusher->trigger($key_export, 'progress', [
                    'message' => "Memproses data ke $key dari $total...",
                    'progress' => $progress
                ]);
            }
        }

        $pusher->trigger($key_export, 'progress', [
            'message' => 'Export selesai!',
            'progress' => 100
        ]);

        return view('export.lead-order-manual', [
            'data' => $lead_data,
            'type' => $type
        ]);
    }

    public function title(): string
    {
        return $this->title;
    }

    protected function mapProducts($products)
    {
        $result = [];
        foreach ($products as $item) {
            $result[] = [
                'sku' => @$item->product?->sku,
                'product_name' => @$item->product_name,
                'price' => $item->price,
                'qty' => $item->qty,
                'tax_amount' => $item->tax_amount,
                'discount_amount' => $item->discount_amount,
                'subtotal' => $item->subtotal,
                'price_nego' => $item->price_nego,
                'total_price' => $item->total,
            ];
        }
        return $result;
    }
}
