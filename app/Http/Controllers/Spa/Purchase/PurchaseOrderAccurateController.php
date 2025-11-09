<?php

namespace App\Http\Controllers\Spa\Purchase;

use App\Http\Controllers\Controller;
use App\Exports\PurchaseOrderExport;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\InventoryItem;
use App\Models\InventoryProductStock;
use App\Models\LeadMaster;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\ProductNeed;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\PurchaseBilling;
use App\Models\PurchaseInvoiceEntry;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderAccurate;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderStockOpname;
use App\Models\Asset;
use App\Models\Vendor;
use App\Models\PoPrMapping;
use App\Models\Product;
use App\Models\BarcodeSubmitLog;
use App\Models\ProductCarton;
use App\Models\Warehouse;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

class PurchaseOrderAccurateController extends Controller
{
    public function index($purchase_order_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPurchaseOrder(Request $request) 
    {
        $search = $request->search;
        $status = $request->status;
        // $pr_type = $request->pr_type;
        $account_id = $request->account_id;
        $tanggal_transaksi = $request->tanggal_transaksi;
        $order =  PurchaseOrderAccurate::query();
        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('po_number', 'like', "%$search%");
                $query->orWhere('vendor_code', 'like', "%$search%");
                // $query->orWhereHas('createdBy', function ($query) use ($search) {
                    // $query->where('name', 'like', "%$search%");
                // });
            });
        }

        // cek switch account
        if ($account_id) {
            $order->where('company_id', $account_id);
        }

        if ($status) {
            $order->where('status', $status == 10 ? 0 : $status);
        }

        if ($tanggal_transaksi) {
            // Assuming $tanggal_transaksi is an array with two elements: start date and end date
            $startDate = $tanggal_transaksi[0];
            $endDate = $tanggal_transaksi[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $order->whereBetween('created_date', [$startDate, $endDate]);
        }


        $orders = $order->orderByRaw($this->getStatus())->orderBy('created_date', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'List Purchase Order'
        ]);
    }

    public function getStatus()
    {
        $orderMapping = [
            '0' => 0,
            '1' => 2,
            '2' => 3,
            '3' => 7,
            '4' => 6,
            '5' => 1,
            '6' => 8,
            '7' => 5,
            '8' => 9,
            '9' => 4
        ];

        // Convert the mapping to a CASE SQL statement
        $orderByCase = 'CASE status';
        foreach ($orderMapping as $status => $order) {
            $orderByCase .= " WHEN '{$status}' THEN {$order}";
        }
        $orderByCase .= ' ELSE 10 END';
        return $orderByCase;
    }

    public function detailPurchaseOrder($purchase_order_id)
    {
        $order = PurchaseOrderAccurate::find($purchase_order_id);

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Detail Purchase Order'
        ]);
    }

    public function updatePurchaseOrder(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'brand_id'  => $request->brand_id,
                'vendor_code'  => $request->vendor_code,
                'vendor_name'  => $request->vendor_name,
                'payment_term_id'  => $request->payment_term_id,
                'warehouse_id'  => $request->warehouse_id,
                'warehouse_user_id'  => $request->warehouse_user_id,
                'tax_id'  => $request->tax_id,
                'currency'  => $request->currency ? $request->currency : 'Rp',
                'notes'  => $request->notes,
                'status'  => $request->status,
                'type_po' => $request->type_po,
                'channel' => $request->channel,
            ];
            $row = PurchaseOrder::find($purchase_order_id);
            $row->update($data);

            //save vendor if vendor tidak ada
            $vendor = Vendor::where('vendor_code', $request->vendor_code)->first();
            if (empty($vendor)) {
                $data_vendor = [
                    'name' => $request->vendor_name,
                    'vendor_code' => $request->vendor_code,
                ];
                $master = Vendor::create($data_vendor);
            }

            if ($request->items && is_array($request->items)) {
                foreach ($request->items as $key => $value) {
                    $datas = [
                        'purchase_order_id' => $purchase_order_id,
                        'product_id' => $value['product_id'],
                        'tax_id' => $value['tax_id'],
                        'price' => $value['price'],
                        'qty' => $value['qty'],
                    ];
                    if (isset($value['uom'])) {
                        $datas['uom'] = $value['uom'];
                    }
                    PurchaseOrderItem::updateOrCreate(['id' => $value['id']], $datas);
                }
            }

            if ($request->status == 5) {
                createNotification(
                    'POCP200',
                    [
                        'user_id' => auth()->user()->id
                    ],
                    [
                        'user' => $row->created_by_name,
                    ],
                    ['brand_id' => 1]
                );
                createNotification(
                    'PORA200',
                    [],
                    [
                        'user_created' => $row->created_by_name,
                        'po_number' => $row->po_number,

                    ],
                    ['brand_id' => 1]
                );
            }
            $dataLog = [
                'log_type' => '[fis-dev]purchase_order',
                'log_description' => 'Update Purchase Order - ' . $row->po_number,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Purchase Order Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Purchase Order Gagal Disimpan'
            ], 400);
        }
    }

    public function syncPurchaseOrderAccurate()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $apiUrl = 'https://zeus.accurate.id/accurate/api/purchase-order/list.do?fields=id%2Cnumber%2C%2CapprovalStatus%2CavailableDownPayment%2Cbranch%2CcashDiscPercent%2CcashDiscount%2CcharField1%2CcharField10%2CcharField2%2CcharField3%2CcharField4%2CcharField5%2CcharField6%2CcharField7%2CcharField8%2CcharField9%2CcreatedByUserName%2Ccurrency%2CdateField1%2CdateField2%2Cdescription%2CdppAmount%2Cfob%2Cid%2CinclusiveTax%2ClastUpdate%2Cnumber%2CnumericField1%2CnumericField10%2CnumericField2%2CnumericField3%2CnumericField4%2CnumericField5%2CnumericField6%2CnumericField7%2CnumericField8%2CnumericField9%2CorderPrintedTime%2CpaymentTerm%2CprintedByUser%2Crate%2CshipDate%2Cshipment%2Cstatus%2CstatusName%2Ctax1Amount%2Ctax2Amount%2Ctax3Amount%2Ctax4Amount%2Ctaxable%2CtotalAmount%2CtotalDownPayment%2CtotalDownPaymentUsed%2CtotalExpense%2CtransDate%2Cvendor&sp.pageSize=200';
        $accessToken = getenv('ACCURATE_ACCESS_TOKEN');
        $secretKey = getenv('ACCURATE_SECRET_KEY');

        if (!$accessToken || !$secretKey) {
            return response()->json(['error' => 'Access Token atau Secret Key tidak ditemukan di .env']);
        }
 
        $timestamp = (string)(time() * 1000);
        $signature = hash_hmac('sha256', $timestamp, $secretKey);

        $headers = [
            "Authorization: Bearer $accessToken",
            "x-api-timestamp: $timestamp",
            "x-api-signature: $signature"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return response()->json(['error' => 'Curl error: ' . curl_error($ch)]);
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if ($data === null) {
            return response()->json(['error' => 'JSON Decode Error: ' . json_last_error_msg()]);
        }

        if (!isset($data['s']) || $data['s'] == false) {
            return response()->json(['error' => 'API Error', 'details' => $data['d'] ?? 'Unknown error']);
        }

        $newDataCount = 0;

        foreach ($data['d'] as $order) {
            $existingOrder = DB::table('purchase_order_accurates')
                ->where('po_number', $order['number'])
                ->exists();

            if (!$existingOrder) {
                $data = [
                    'id_acc' => $order['id'] ?? null,
                    'po_number' => $order['number'] ?? null,
                    'vendor_code' => $order['vendor']['vendorNo'] ?? null,
                    'vendor_name' => $order['vendor']['name'] ?? null,
                    'amount' => $order['totalAmount'] ?? null,
                    'branch_name' => $order['branch']['name'] ?? null,
                    'createdby' => $order['createdByUserName'] ?? null,
                    'created_date' => $order['transDate'] ?? null,
                    'payment_term' => $order['paymentTerm']['name'] ?? null,
                    'status_po' => $order['status'] ?? null,
                    'created_on' => now(),
                    'updated_on' => now(),
                    'company_id' => 1,
                ];
                
                DB::table('purchase_order_accurates')->insert($data);                

                $newDataCount++;

                $po_id = $order['id'];
                $detailApiUrl = "https://zeus.accurate.id/accurate/api/purchase-order/detail.do?id=$po_id";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $detailApiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $detailResponse = curl_exec($ch);

                if (curl_errno($ch)) {
                    return response()->json(['error' => 'Curl error: ' . curl_error($ch)]);
                }
                curl_close($ch);

                $detailData = json_decode($detailResponse, true);

                if ($detailData === null) {
                    return response()->json(['error' => 'JSON Decode Error (Detail): ' . json_last_error_msg()]);
                }

                if ($detailData['s'] == false) {
                    return response()->json(['error' => 'API Detail Error: ' . json_encode($detailData['d'])]);
                }

                if (isset($detailData['d']['detailItem']) && is_array($detailData['d']['detailItem'])) {
                    foreach ($detailData['d']['detailItem'] as $detail) {
                        DB::table('purchase_order_accurate_details')->insert([
                            'id_acc' => $po_id,
                            'product' => $detail['detailName'],
                            'sku' => $detail['item']['no'],
                            'satuan_barang' => $detail['itemUnit']['name'] ?? null,
                            'warehouse' => $detail['warehouse']['name'] ?? null,
                            'unit_price' => $detail['unitPrice'],
                            'notes' => $detail['detailNotes'],
                            'total_price' => $detail['totalPrice'], 
                            'qty' => $detail['quantity'],
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'message' => $newDataCount > 0 
                ? "Data berhasil diproses. $newDataCount data baru ditambahkan." 
                : "Tidak ada data baru.",
            'new_records' => $newDataCount
        ]);
    }

    private function sendToBarcodeAPI($row, $productItems)
    {
        $apiUrl = 'https://brcd-testing.flimty.co/api/barcode/purchase';
        $headers = [
            'x-api-key: '. getSetting('BARCODE_TOKEN'),
            'Content-Type: application/json'
        ];
        
        $products = [];
        foreach ($row->items as $item) {
            $productCarton = ProductCarton::find($item->product->product_carton_id);

            $products[] = [
                'moq' => $productCarton->moq,
                'is_valid' => true,
                'master_sku' => $item->product->sku,
                'product_sku' => $productCarton->sku,
                'qty' => $item->qty
            ];
        }

        $warehouse = Warehouse::find($row->warehouse_id);
        $payload = [
            'po_number' => $row->po_number,
            'vendor_code' => $row->vendor_code,
            'user_id' => auth()->user()->id,
            'warehouse_id' => $warehouse->wh_id,
            'message' => $row->notes,
            'products' => $products
        ];
        setSetting('payload_barcode', json_encode($payload));
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $apiUrl);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        $responseData = curl_exec($handle);
        curl_close($handle);
        setSetting('barcode_orca_1', $responseData);
        $response = json_decode($responseData);
        
        
        if (!empty($response)) {
            $data_submit = [
                'po_id' => $row->id,
                'activity' => 'Approve PO',
                'hit_date'  => date('Y-m-d H:i:s'),
                'hit_user'  => auth()->user()->id,
                'status' => (@$response->order_id != '-1' || @$response->success != false)?'Success':'Failed',
                'description' => $response->msg??'-'
            ];
            
            BarcodeSubmitLog::create($data_submit);

            if (isset($response->order_id) && $response->order_id == '-1') {
                return 'Approval Gagal! Sistem gagal untuk submit barcode.';
                // throw new \Exception('Approval Gagal! Sistem gagal untuk submit barcode.');
            } else {
                $data = [
                    'hit_barcode'  => date('Y-m-d H:i:s'),
                    'hit_user'  => auth()->user()->id,
                ];
                $po = PurchaseOrder::find($row->id);
                $po->update($data);
                return 'Success';
            }
        }
        return 'No response from the API';
    }

    public function exportPdf($purchase_order_id = null)
    {
        $purchase = PurchaseOrder::with(['items', 'requisitions'])->find($purchase_order_id);
        return view('print.po', ['data' =>  $purchase]);
    }

    public function export()
    {
        $product = PurchaseOrder::query();

        $file_name = 'convert/FIS-Purchase_order-' . date('d-m-Y') . '.xlsx';

        Excel::store(new PurchaseOrderExport($product), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

}
