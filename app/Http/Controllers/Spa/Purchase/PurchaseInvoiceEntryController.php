<?php

namespace App\Http\Controllers\Spa\Purchase;

use App\Jobs\SubmitSIGpQueue;
use App\Exports\InvoiceEntryExport;
use App\Http\Controllers\Controller;
use App\Models\PurchaseBilling;
use App\Models\PurchaseInvoiceEntry;
use App\Models\PurchaseInvoiceEntryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\PaymentTerm;
use App\Models\Product;
use App\Models\GpBatchId;
use App\Models\MasterTax;
use App\Models\CompanyAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceEntryController extends Controller
{
    public function index($purchase_invoice_entry_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPurchaseInvoiceEntry(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $created_at = $request->tanggal;
        // $account_id = $request->account_id;
        $order =  PurchaseInvoiceEntry::query()->with('billings');

        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('po_number', 'like', "%$search%");
                $query->orWhere('vendor_code', 'like', "%$search%");
                $query->orWhereHas('createdBy', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            });
        }

        // cek switch account
        // if ($account_id) {
        //     $order->where('company_id', $account_id);
        // }

        if ($status) {
            $order->whereIn('status', $status);
        }

        if ($created_at) {
            $order->whereBetween('created_at', $created_at);
        }

        $orders = $order->orderBy('status')->orderBy('created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'List Purchase Order'
        ]);
    }

    function getStatusCard()
    {
        $data = [
            ['label' => 'Outstanding', 'value' => [0, 3], 'count' => PurchaseInvoiceEntry::whereIn('status', [0, 3])->count()],
            ['label' => 'Paid', 'value' => [1], 'count' => PurchaseInvoiceEntry::whereIn('status', [1])->count()],
            ['label' => 'Partial Paid', 'value' => [3], 'count' => PurchaseInvoiceEntry::whereIn('status', [3])->count()],
            ['label' => 'Cancel', 'value' => [2], 'count' => PurchaseInvoiceEntry::whereIn('status', [2])->count()],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function detailPurchaseInvoiceEntry($purchase_invoice_entry_id)
    {
        $order = PurchaseInvoiceEntry::with(['items', 'billings'])->where('id', $purchase_invoice_entry_id)->first();
        if (!empty($order->items)) {
            foreach ($order->items as $itm) {
                $po = PurchaseOrder::find($itm->purchase_order_id);
                $poItem = PurchaseOrderItem::find($itm->purchase_order_item_id);
                $itm['gp_po_number'] = @$po->gp_po_number;
                $itm['gp_received_number'] = @$poItem->gp_received_number;
                $itm['price'] = @$poItem->price;
            }
        }

        if ($order) {
            return response()->json([
                'status' => 'success',
                'data' => $order,
                'message' => 'Detail Purchase Order'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => null,
            'message' => 'Data Tidak Ditemukan'
        ], 404);
    }

    public function listPurchaseInvoiceEntryGetRcvNumber()
    {
        $purchase = PurchaseInvoiceEntry::whereNotNull('received_number')->orderBy('created_at', 'DESC')->select('received_number')->first();
        if ($purchase) {
            $currentYear = now()->format('Y');
            $sequenceNumber = $purchase ? (int)substr($purchase->received_number, -9) + 1 : 1;

            return response()->json([
                'status' => 'error',
                'data' => 'RCV/' . $currentYear . '/' . str_pad($sequenceNumber, 9, '0', STR_PAD_LEFT),
                'message' => 'Data Ditemukan'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => 'RCV/' . date('Y') . '/00000001',
            'message' => 'Data Tidak Ditemukan'
        ]);
    }

    public function getPaymentNumber()
    {
        $purchase = PurchaseBilling::whereNotNull('payment_number')->orderBy('created_at', 'DESC')->select('payment_number')->first();
        if ($purchase) {
            $currentYear = now()->format('Y');
            $sequenceNumber = $purchase ? (int)substr($purchase->received_number, -9) + 1 : 1;
            return 'PAY/' . $currentYear . '/' . str_pad($sequenceNumber, 9, '0', STR_PAD_LEFT);
        } else {
            return 'PAY/' . date('Y') . '/00000001';
        }
    }

    public function getPurchaseOrderByVendorCode($vendor_code)
    {
        $purchase = PurchaseOrder::with(['items' => function ($query) {
            return $query->where('invoice_entry', 0)->whereNotNull('do_number');
        }])->where('vendor_code', $vendor_code)->get();
        if ($purchase) {
            return response()->json([
                'status' => 'success',
                'data' => $purchase,
                'message' => 'Data Ditemukan'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => [],
            'message' => 'Data Tidak Ditemukan'
        ]);
    }

    public function createInvoiceEntry(Request $request)
    {
        $isPaid = count($request->items) == count($request->billings) ? 1 : $request->status;
        if ($request->purchase_invoice_entry_id) {
            $invoiceEntryExist = PurchaseInvoiceEntry::find($request->purchase_invoice_entry_id);
        } else {
            $docNumberExist = PurchaseInvoiceEntry::where('vendor_doc_number', $request->vendor_doc_number)->first(['vendor_doc_number']);
            if ($docNumberExist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Doc Number Sudah Terdaftar'
                ], 400);
            }
        }
        $invoiceEntry = PurchaseInvoiceEntry::updateOrCreate(['id' => $request->purchase_invoice_entry_id], [
            'received_number' => $request->received_number,
            'vendor_doc_number' => $request->vendor_doc_number,
            'invoice_date' => $request->invoice_date,
            'vendor_id' => $request->vendor_id,
            'vendor_name' => $request->vendor_name,
            'batch_id' => $request->batch_id,
            'payment_term_id' => $request->payment_term_id,
            'submit_gp' => 0,
            'status' => $isPaid,
            'tax_id' => $request->tax_id,
            'type_invoice' => $request->type_invoice,
            'created_by' => (!$request->purchase_invoice_entry_id) ? auth()->user()->id : $invoiceEntryExist->created_by
        ]);

        $batch = GpBatchId::updateOrCreate(['batch_code' => $request->batch_id]);

        // if (!$request->purchase_invoice_entry_id) {
        //     $invoiceEntry['created_by'] = auth()->user()->id;
        // }

        if ($invoiceEntry->status > 0) {
            foreach ($request->items as $key => $item) {
                PurchaseInvoiceEntryItem::updateOrCreate(['purchase_invoice_entry_id' => $invoiceEntry->id ?? $request->purchase_invoice_entry_id], [
                    'purchase_invoice_entry_id' => $invoiceEntry->id,
                    'purchase_order_id' => $item['id'],
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'uom' => $item['uom'],
                    'qty' => $item['qty'],
                    'sku' => $item['sku'],
                    'tax_code' => $item['tax'],
                    'extended_cost' => $item['extended_cost'],
                ]);
                $uid_invoice = hash('crc32', Carbon::now()->format('U'));

                $row = PurchaseOrderItem::find($item['purchase_order_item_id']);
                if ($row) {
                    // $data = ['invoice_entry' => $request->invoice_entry];
                    // insert invoice entry
                    if (!$request->purchase_invoice_entry_id) {
                        $data['confirm_by'] = auth()->user()->id;
                        $data['invoice_date'] = Carbon::now();
                        $data['uid_invoice'] = $uid_invoice;
                    }
                    $data['invoice_entry'] = 1;
                    $data['vendor_doc_number'] = $request->vendor_doc_number;

                    $row->update($data);
                }
            }
        } else {
            foreach ($request->items as $key => $item) {
                PurchaseInvoiceEntryItem::create([
                    'purchase_invoice_entry_id' => $invoiceEntry->id,
                    'purchase_order_id' => $item['id'],
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'uom' => $item['uom'],
                    'qty' => $item['qty'],
                    'sku' => $item['sku'],
                    'tax_code' => $item['tax'],
                    'extended_cost' => $item['extended_cost'],
                ]);

                $uid_invoice = hash('crc32', Carbon::now()->format('U'));

                $row = PurchaseOrderItem::find($item['purchase_order_item_id']);
                if ($row) {
                    // $data = ['invoice_entry' => $request->invoice_entry];
                    // insert invoice entry
                    $data['invoice_entry'] = 1;
                    $data['vendor_doc_number'] = $request->vendor_doc_number;

                    $row->update($data);
                }
            }
        }

        foreach ($request->billings as $key => $billing) {
            $data = [
                'payment_number' => $this->getPaymentNumber(),
                'purchase_order_id' =>  $item['id'],
                'nama_bank' => $billing['nama_bank'],
                'received_number' => $invoiceEntry->received_number,
                'no_rekening' => $billing['no_rekening'],
                'nama_pengirim' => @$billing['nama_pengirim'],
                'jumlah_transfer' => $billing['jumlah_transfer'],
                'tax_amount' => $billing['tax_amount'],
                'sumberdana' => @$billing['sumberdana'],
                'no_rekening_sumberdana' => @$billing['no_rekening_sumberdana'],
                'status' => 0
            ];
            if (!$request->purchase_invoice_entry_id) {
                $data['created_by'] = auth()->user()->id;
            }

            if ($billing['bukti_transfer']) {
                $file = $this->uploadImage($billing, 'bukti_transfer');
                $data['bukti_transfer'] = $file;
            }

            PurchaseBilling::updateOrCreate([
                'purchase_order_id' =>  $item['id'],
                'received_number' => $billing['received_number'],
            ], $data);
        }



        return response()->json([
            'status' => 'success',
            'message' => 'Data Berhasil Disimpan'
        ]);
    }

    public function updateBillingInvoiceEntry(Request $request)
    {
        $data = [
            'payment_number' => $this->getPaymentNumber(),
            'purchase_order_id' =>   $request->purchase_order_id,
            'created_by' => auth()->user()->id,
            'nama_bank' => $request->nama_bank,
            'received_number' => $request->received_number,
            'no_rekening' => $request->no_rekening,
            'nama_pengirim' => $request->nama_pengirim,
            'jumlah_transfer' => $request->jumlah_transfer,
            'tax_amount' => $request->tax_amount,
            'sumberdana' => $request->sumberdana,
            'no_rekening_sumberdana' => $request->no_rekening_sumberdana,
            'status' => 0
        ];

        if ($request->bukti_transfer) {
            $file = $this->uploadImage($request, 'bukti_transfer');
            $data['bukti_transfer'] = $file;
        }
        $invoiceEntry = PurchaseInvoiceEntry::find($request->purchase_invoice_entry_id);
        if ($invoiceEntry) {
            $invoiceEntry->update(['status' => 1]);
        }
        PurchaseBilling::create($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Data Berhasil Disimpan'
        ]);
    }

    public function updateStatusInvoiceEntry(Request $request)
    {
        $invoiceEntry = PurchaseInvoiceEntry::find($request->purchase_invoice_entry_id);

        if ($invoiceEntry) {
            if ($invoiceEntry->type_invoice == 'product') {
                foreach ($invoiceEntry->items as $key => $item) {
                    $uid_invoice = hash('crc32', Carbon::now()->format('U'));

                    $row = PurchaseOrderItem::find($item->purchase_order_item_id);
                    // $data = ['invoice_entry' => $request->invoice_entry];
                    // insert invoice entry
                    $data['confirm_by'] = auth()->user()->id;
                    $data['invoice_date'] = Carbon::now();
                    $data['uid_invoice'] = $uid_invoice;
                    $data['invoice_entry'] = 1;
                    $data['vendor_doc_number'] = $invoiceEntry->vendor_doc_number;

                    $row->update($data);
                }
            }
            $invoiceEntry->update(['status' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data Berhasil Disimpan'
        ]);
    }

    public function cancelPurchaseInvoiceEntry(Request $request)
    {
        $invoiceEntry = PurchaseInvoiceEntry::find($request->purchase_invoice_entry_id);

        if ($invoiceEntry) {
            if ($invoiceEntry->type_invoice == 'product') {
                foreach ($invoiceEntry->items as $key => $item) {
                    $row = PurchaseOrderItem::where('invoice_entry', 1)->find($item->purchase_order_item_id);

                    $row->update(['invoice_entry' => 0]);
                }
            }
            $invoiceEntry->update(['status' => 2]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data Berhasil Disimpan'
        ]);
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }
        $file = Storage::disk('s3')->put('upload/purchase/billing', $request[$path], 'public');
        return $file;
    }

    function exportPurchaseInvoiceEntry(Request $request)
    {

        $file_name = 'invoice-entry' . date('d-m-Y') . '.xlsx';

        Excel::store(new InvoiceEntryExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'export success'
        ]);
    }

    public function submitGP(Request $request)
    {
        $invoiceEntry = PurchaseInvoiceEntry::whereIn('id', $request->items)->get();
        $header = [];
        $line = [];
        $data_submit = [];
        foreach ($invoiceEntry as $key => $item) {
            $company_id = ($item->company_id) ? $item->company_id : auth()->user()->company_id;
            $company = CompanyAccount::find($company_id, ['account_code']);
            $tax = MasterTax::find($item->tax_id);
            if ($item->status_gp == "submited") {
                return response()->json([
                    'message' => 'Data invoice entry sudah pernah di submit',
                    'status' => 'failed',
                ]);
            }
            $paymentTerm = PaymentTerm::find($item->payment_term_id);

            if ($item->type_invoice == 'product') {
                $data_submit[$item->id]['headers'][] =
                    [
                        'VENDOR_NUMBER' => $item->vendor_doc_number,
                        'VENDOR_ID' => $item->vendor_id,
                        'VENDOR_NAME' => $item->vendor_name,
                        'INVOICE_DATE' => $item->invoice_date,
                        'PAYMENT_TERM' => $paymentTerm->name,
                        'TAX_ID' => @$tax->tax_code ?? 'VAT IN',
                    ];

                $invoiceEntryItem = PurchaseInvoiceEntryItem::where('purchase_invoice_entry_id', $item->id)->get();
                foreach ($invoiceEntryItem as $key => $row) {
                    $poItem = PurchaseOrderItem::find($row->purchase_order_item_id);
                    $purchase = PurchaseOrder::find($row->purchase_order_id);
                    $product = Product::find($row->product_id);

                    $data_submit[$item->id]['body'][] =
                        [
                            'PO_NUMBER' => $purchase->gp_po_number,
                            'RECEIVING_NUMBER' => $poItem->gp_received_number,
                            'PRODUCT_ID' => $product->sku,
                            'UOM' => $row->uom,
                            'QTY' => $row->qty,
                        ];
                }

                $submitLog = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'purchasing-invoice-entry',
                    'vat' => $request->vat_value,
                    'tax' => $item->tax_id ?? 'VAT IN',
                    'ref_id' => $item->id
                ]);
            } else {
                $extended_cost = 0;
                $qty = 0;
                $invoiceEntryItem = PurchaseInvoiceEntryItem::where('purchase_invoice_entry_id', $item->id)->get();
                foreach ($invoiceEntryItem as $key => $row) {
                    $extended_cost += $row->extended_cost;
                    $qty += $row->qty;
                }
                $data_submit[$item->id]['headers'][] =
                    [
                        'DOCUMENT_TYPE' => '1',
                        'VENDOR_ID' => $item->vendor_id,
                        'VENDOR_NAME' => $item->vendor_name,
                        'CURRENCY' => 'IDR',
                        'DOC_NUMBER' => $item->vendor_doc_number,
                        'TYPE_AMOUNT' => $qty * $extended_cost,
                        'TRADE_DISCOUNT' => 0,
                        'FREIGHT' => 0,
                        'MISCELLANNOUS' => 0,
                        'TAX_ID' => @$tax->tax_code ?? 'VAT IN',
                    ];

                $submitLog = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'payables-entry', //jasa
                    'vat' => $request->vat_value,
                    'tax' => @$item->tax_id ?? 'VAT IN',
                    'ref_id' => $item->id
                ]);
            }
        }

        if ($item->type_invoice == 'product') {
            $body = [];
            foreach ($data_submit as $key => $value) {
                $body[] = json_encode([
                    'header' => $value['headers'],
                    'line' => $value['body'],
                ]);
            }

            $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
            foreach ($body as $key => $body_value) {
                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
                } else {
                    $order_log_id = $submitLog->id;

                    setSetting('GP_BODY_' . $order_log_id, $body_value);

                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/PI/PIEntry',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => $body_value,
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $response);
                            foreach ($invoiceEntry as $key => $purchase) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id,
                                    'status' => 'failed',
                                    'error_message' => $response
                                ]);
                            }

                            return;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, curl_error($curl));
                            foreach ($invoiceEntry as $key => $purchase) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id,
                                    'status' => 'failed',
                                    'error_message' => curl_error($curl)
                                ]);
                            }

                            return;
                        }

                        setSetting('GP_RESPONSE_INVOICE_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if ($responseJSON['code'] == 200) {
                                foreach ($invoiceEntry as $key => $invoice) {
                                    if (!$invoice->status_gp) {
                                        $invoice->update(['status_gp' => 'submited', 'gp_invoice_number' => $responseJSON['data'][0]['invoicE_NUMBER']]);
                                    }

                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $invoice->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $invoice->id,
                                        'status' => 'success',
                                        'error_message' => null
                                    ]);
                                    $submitLog->update(['ref_id' => $invoice->id]);
                                }
                                return;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($invoiceEntry as $key => $purchase) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id,
                                    'status' => 'failed',
                                    'error_message' => $responseJSON['desc']
                                ]);
                            }
                        }
                    } catch (ClientException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $responseBodyAsString);
                        foreach ($invoiceEntry as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $responseBodyAsString
                            ]);
                        }
                    }
                }
            }
        } else {
            $body = [];
            foreach ($data_submit as $key => $value) {
                $body[] = json_encode([
                    'header' => $value['headers']
                ]);
            }

            $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
            foreach ($body as $key => $body_value) {
                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
                } else {
                    $order_log_id = $submitLog->id;

                    setSetting('GP_BODY_' . $order_log_id, $body_value);

                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/PTE/PTEEntry',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => $body_value,
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $response);
                            foreach ($invoiceEntry as $key => $purchase) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id,
                                    'status' => 'failed',
                                    'error_message' => $response
                                ]);
                            }

                            return;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, curl_error($curl));
                            foreach ($invoiceEntry as $key => $purchase) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id,
                                    'status' => 'failed',
                                    'error_message' => curl_error($curl)
                                ]);
                            }

                            return;
                        }

                        setSetting('GP_RESPONSE_PAYABLES_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if ($responseJSON['code'] == 200) {
                                foreach ($invoiceEntry as $key => $invoice) {
                                    if (!$invoice->status_gp) {
                                        $invoice->update(['status_gp' => 'submited', 'status_payable_gp' => 'submited', 'gp_invoice_number' => $responseJSON['data'][0]['voucheR_NUMBER'], 'gp_payable_number' => $responseJSON['data'][0]['voucheR_NUMBER']]);
                                    }

                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $invoice->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $invoice->id,
                                        'status' => 'success',
                                        'error_message' => null
                                    ]);

                                    $submitLog->update(['ref_id' => $invoice->id]);
                                }

                                return;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($invoiceEntry as $key => $purchase) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $purchase->id,
                                    'status' => 'failed',
                                    'error_message' => $responseJSON['desc']
                                ]);
                            }
                        }
                    } catch (ClientException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        setSetting('GP_RESPONSE_ERROR_PAYABLES_' . $order_log_id, $responseBodyAsString);
                        foreach ($invoiceEntry as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $responseBodyAsString
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function submitPaymentGp(Request $request)
    {
        $invoiceEntry = PurchaseInvoiceEntry::whereIn('id', $request->items)->get();
        $billings = PurchaseBilling::whereIn('id', $request->billingIds)->whereNull('status_gp')->get();
        $body = [];
        foreach ($billings as $bill) {
            $company_id = ($bill->company_id) ? $bill->company_id : auth()->user()->company_id;
            $company = CompanyAccount::find($company_id, ['account_code']);
            if ($bill->status == 1) {
                $po = PurchaseOrder::find($bill->purchase_order_id);
                $inv_entry = PurchaseInvoiceEntry::where('received_number', $bill->received_number)->first();

                $body[] =
                    [
                        'PURCHASE_ORDER_ID' => @$po->gp_po_number,
                        'VENDOR_CODE' => (!empty($po) ? $po->vendor_code : $inv_entry->vendor_id),
                        'VENDOR_NAME' => (!empty($po) ? $po->vendor_name : $inv_entry->vendor_name),
                        'PAYMENT_DATE' => date('Y-m-d', strtotime($bill->created_at)),
                        'NAMA_BANK' => $bill->nama_bank,
                        'JUMLAH_TRANSFER' => $bill->jumlah_transfer,
                        'TYPE_TAX' => ($inv_entry->type_invoice == 'jasa') ? 1 : 0,
                    ];

                $submitLog = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'manual-payment-entry',
                    'vat' => '',
                    'tax' => '',
                    'ref_id' => $bill->id
                ]);
            }
        }

        $isUseQueue = getSetting('GP_SUBMIT_QUEUE');

        foreach ($body as $key => $body_value) {
            if ($isUseQueue) {
                SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body, $request->ids)->onQueue('queue-log');
            } else {
                $order_log_id = $submitLog->id;

                setSetting('GP_BODY_' . $order_log_id, json_encode($body_value));

                try {
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => getSetting('GP_URL') . '/PMP/PMPEntry',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $body_value,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: multipart/form-data', // Mengubah Content-Type
                            'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $responseJSON = json_decode($response, true);
                    // check is string
                    if (!$responseJSON && is_string($response)) {
                        setSetting('GP_RESPONSE_ERROR_BILLING_' . $order_log_id, $response);
                        foreach ($invoiceEntry as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $response
                            ]);
                        }

                        return;
                    }

                    // Check if any error occured
                    if (curl_errno($curl)) {
                        setSetting('GP_RESPONSE_ERROR_BILLING_' . $order_log_id, curl_error($curl));
                        foreach ($invoiceEntry as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => curl_error($curl)
                            ]);
                        }

                        return;
                    }

                    setSetting('GP_RESPONSE_BILLING_' . $order_log_id, json_encode($responseJSON));
                    if (isset($responseJSON['code'])) {
                        if ($responseJSON['code'] == 201) {
                            foreach ($invoiceEntry as $key => $invoice) {
                                $billings = PurchaseBilling::where('received_number', $invoice->received_number)->get();
                                foreach ($billings as $bill) {
                                    if (!$bill->status_gp) {
                                        $bill->update(['status_gp' => 'submited', 'gp_payment_number' => $responseJSON['data'][0]['paymenT_NUMBER']]);
                                    }
                                }

                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $invoice->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $invoice->id,
                                    'status' => 'success',
                                    'error_message' => null
                                ]);

                                $submitLog->update(['ref_id' => $bill->id]);
                            }

                            return;
                        }
                    }

                    if (isset($responseJSON['desc'])) {
                        foreach ($invoiceEntry as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['desc']
                            ]);
                        }
                    }
                } catch (ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    setSetting('GP_RESPONSE_ERROR_PAYMENT_' . $order_log_id, $responseBodyAsString);
                    foreach ($invoiceEntry as $key => $purchase) {
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $purchase->id
                        ], [
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $purchase->id,
                            'status' => 'failed',
                            'error_message' => $responseBodyAsString
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }
}
