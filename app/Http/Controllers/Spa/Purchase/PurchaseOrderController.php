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
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderStockOpname;
use App\Models\PurchaseRequitition;
use App\Models\PurchaseRequititionItem;
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

class PurchaseOrderController extends Controller
{
    public function index($purchase_order_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPurchaseOrder(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $pr_type = $request->pr_type;
        $account_id = $request->account_id;
        $tanggal_transaksi = $request->tanggal_transaksi;
        $order =  PurchaseOrder::query()->with('items');
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
        if ($account_id) {
            $order->where('company_id', $account_id);
        }

        if ($status) {
            $order->where('status', $status == 10 ? 0 : $status);
        }

        if ($pr_type) {
            $order->where('pr_type', $pr_type);
        }

        if ($tanggal_transaksi) {
            // Assuming $tanggal_transaksi is an array with two elements: start date and end date
            $startDate = $tanggal_transaksi[0];
            $endDate = $tanggal_transaksi[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $order->whereBetween('created_at', [$startDate, $endDate]);
        }


        $orders = $order->orderByRaw($this->getStatus())->orderBy('created_at', 'desc')->paginate($request->perpage);
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
        $order = PurchaseOrder::with(['items', 'billings', 'barcodes', 'requisitions', 'barcodelogs'])->find($purchase_order_id);

        return response()->json([
            'status' => 'success',
            'data' => $order,
            'message' => 'Detail Purchase Order'
        ]);
    }

    function updatePurchaseTax(Request $request, $purchase_order_id)
    {
        $purchase = PurchaseOrder::find($purchase_order_id);
        if ($purchase) {
            $purchase->update(['tax_id' => $request->tax_id]);
            $purchase->items()->update(['tax_id' => $request->tax_id]);


            return response()->json([
                'status' => 'success',
                'data' => $purchase,
                'message' => 'Tax berhasil disimpan'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'Tax gagal disimpan'
        ], 400);
    }

    public function savePurchaseOrder(Request $request)
    {
        try {
            DB::beginTransaction();
            $account_id = $request->account_id;
            $company = CompanyAccount::where('status', 1)->first();
            $companyId = $company ? $company->id : null;
            if ($account_id) {
                $companyId = $account_id;
            }

            $data = [
                'po_number'  => $this->generatePoNumber(),
                'brand_id'  => $request->brand_id,
                'vendor_code'  => $request->vendor_code,
                'vendor_name'  => $request->vendor_name,
                'created_by'  => auth()->user()->id,
                'payment_term_id'  => $request->payment_term_id,
                'warehouse_id'  => $request->warehouse_id,
                'warehouse_user_id'  => $request->warehouse_user_id,
                'tax_id'  => $request->tax_id,
                'currency'  => $request->currency ? $request->currency : 'Rp',
                'notes'  => $request->notes,
                'status'  => $request->status,
                'type_po' => $request->type_po,
                'channel' => $request->channel,
                'channel' => $request->channel,
                'company_id' => $companyId,
                'pr_type' => $request->pr_type,
                'pr_number' => @$request->pr_number,
                'has_barcode' => @$request->has_barcode
            ];

            $order = PurchaseOrder::create($data);

            //save vendor if vendor tidak ada
            $vendor = Vendor::where('vendor_code', $request->vendor_code)->first();
            if (empty($vendor)) {
                $data_vendor = [
                    'name' => $request->vendor_name,
                    'vendor_code' => $request->vendor_code,
                ];
                $master = Vendor::create($data_vendor);
            }

            if ($request->pr_type == 'PR') {
                $pr = PurchaseRequitition::where('pr_number', $request->pr_number)->first();
                if ($pr) {
                    $pr->update([
                        'is_po_created' => 1,
                        'purchase_order_id' => $order->id
                    ]);
                }
            }

            if ($request->items && is_array($request->items)) {
                foreach ($request->items as $key => $value) {
                    $check_carton = Product::where('id', $value['product_id'])->whereNotNull('product_carton_id')->first();
                    if ($request->has_barcode && empty($check_carton)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Maaf produk yang anda pilih belum memiliki produk karton, silakan pilih produk lain.'
                        ], 400);
                    }
                    $datas = [
                        'product_id' => $value['product_id'],
                        'tax_id' => $value['tax_id'],
                        'qty' => $value['qty'],
                        'qty_diterima' => 0,
                        'is_master' => 1,
                    ];

                    if (isset($value['uom'])) {
                        $datas['uom'] = $value['uom'];
                    }

                    if (isset($value['price'])) {
                        $datas['price'] = $value['price'];
                    }

                    $order->items()->create($datas);
                }
            }

            if ($request->status == 5) {
                createNotification(
                    'POCP200',
                    [
                        'user_id' => auth()->user()->id
                    ],
                    [
                        'user' => $order->created_by_name,
                    ],
                    ['brand_id' => 1]
                );
                createNotification(
                    'PORA200',
                    [],
                    [
                        'user_created' => $order->created_by_name,
                        'po_number' => $order->po_number,

                    ],
                    ['brand_id' => 1]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]purchase_order',
                'log_description' => 'Create Purchase Order - ' . $order->po_number,
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
                'message' => 'Data Purchase Order Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function generatePurchaseOrder(Request $request)
    {
        try {
            DB::beginTransaction();
            $account_id = $request->account_id;
            $company = CompanyAccount::where('status', 1)->first();
            $companyId = $company ? $company->id : null;
            if ($account_id) {
                $companyId = $account_id;
            }

            $pr = PurchaseRequitition::where('uid_requitition', $request->uid_requitition)->first();
            $pr_items = PurchaseRequititionItem::where('purchase_requitition_id', $pr->id)->get();
            $data = [
                'po_number'  => $this->generatePoNumber(),
                'brand_id'  => $pr->brand_id ?? $request->brand_id,
                'vendor_code'  => $pr->vendor_code ?? $request->vendor_code,
                'vendor_name'  => $pr->vendor_name ?? $request->vendor_name,
                'created_by'  => auth()->user()->id,
                'payment_term_id'  => $pr->payment_term_id,
                'warehouse_id'  => $request->warehouse_id,
                'warehouse_user_id'  => @$request->warehouse_pic['value'],
                'tax_id'  => @$request->tax_id,
                'currency'  => $request->currency ? $request->currency : 'Rp',
                'notes'  => $request->notes,
                'status'  => 0,
                'type_po' => $request->type_po,
                'channel' => @$request->channel,
                'company_id' => $companyId,
                'pr_type' => 'PR',
                'pr_number' => @$pr->pr_number
            ];
            $order = PurchaseOrder::create($data);

            //save vendor if vendor tidak ada
            $vendor = Vendor::where('vendor_code', $request->vendor_code)->first();
            if (empty($vendor)) {
                $data_vendor = [
                    'name' => $request->vendor_name,
                    'vendor_code' => $request->vendor_code,
                ];
                $master = Vendor::create($data_vendor);
            }

            $pr_update = PurchaseRequitition::where('pr_number', $pr->pr_number)->first();

            if ($pr_update) {
                $pr_update->update([
                    'is_po_created' => 1,
                    'purchase_order_id' => $order->id
                ]);
            }

            if ($pr_items) {
                foreach ($pr_items as $value) {
                    $datas = [
                        'product_id' => $value['item_id'],
                        'tax_id' => @$value['tax_id'],
                        'qty' => $value['item_qty'],
                        'qty_diterima' => 0,
                        'is_master' => 1,
                    ];
                    if (isset($value['uom'])) {
                        $datas['uom'] = $value['uom'];
                    }
                    if (isset($value['price'])) {
                        $datas['price'] = $value['price'];
                    }
                    $order->items()->create($datas);
                }
            }
            if ($request->status == 5) {
                createNotification(
                    'POCP200',
                    [
                        'user_id' => auth()->user()->id
                    ],
                    [
                        'user' => $order->created_by_name,
                    ],
                    ['brand_id' => 1]
                );
                createNotification(
                    'PORA200',
                    [],
                    [
                        'user_created' => $order->created_by_name,
                        'po_number' => $order->po_number,

                    ],
                    ['brand_id' => 1]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]purchase_order',
                'log_description' => 'Create Purchase Order - ' . $order->po_number,
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
                'message' => 'Data Purchase Order Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function generateBulkPurchaseOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            $account_id = $request->account_id;
            $company = CompanyAccount::where('status', 1)->first();
            $companyId = $company ? $company->id : null;
            if ($account_id) {
                $companyId = $account_id;
            }

            $ids = $request->ids; // Mendapatkan array uid_requitition

            // Generate PO Number
            $poNumber = $this->generatePoNumber();

            // Create Purchase Order
            $data = [
                'po_number'  => $poNumber,
                'brand_id'  => $request->brand_id,
                'vendor_code'  => $request->vendor_code,
                'vendor_name'  => $request->vendor_name,
                'created_by'  => auth()->user()->id,
                'payment_term_id'  => $request->payment_term_id,
                'warehouse_id'  => $request->warehouse_id,
                'warehouse_user_id'  => @$request->warehouse_pic['value'],
                'tax_id'  => @$request->tax_id,
                'currency'  => $request->currency ? $request->currency : 'Rp',
                'notes'  => $request->notes,
                'status'  => 0,
                'type_po' => $request->type_po,
                'channel' => @$request->channel,
                'company_id' => $companyId,
                'pr_type' => 'PR',
                'pr_number' => null // Ini akan diupdate nanti jika perlu
            ];

            $order = PurchaseOrder::create($data);

            // Save vendor if vendor tidak ada
            $vendor = Vendor::where('vendor_code', $request->vendor_code)->first();
            if (empty($vendor)) {
                $data_vendor = [
                    'name' => $request->vendor_name,
                    'vendor_code' => $request->vendor_code,
                ];
                $master = Vendor::create($data_vendor);
            }

            // Mapping to store combined items
            $combinedItems = [];

            // Process each PR and insert into PoPrMapping
            foreach ($ids as $id_pr) {
                $pr = PurchaseRequitition::where('id', $id_pr)->first();
                if (!$pr) continue;

                $po = PurchaseOrder::where('id', $order->id)->first();
                $po->update([
                    'brand_id' => $pr->brand_id
                ]);

                $pr_items = PurchaseRequititionItem::where('purchase_requitition_id', $pr->id)->get();

                // Insert ke PoPrMapping
                PoPrMapping::create([
                    'purchase_order_id' => $order->id,
                    'purchase_requitition_id' => $pr->id,
                    'po_number' => $order->po_number,
                    'pr_number' => $pr->pr_number
                ]);

                // Update Purchase Requitition
                $pr->update([
                    'is_po_created' => 1,
                    'purchase_order_id' => $order->id
                ]);

                // Process items and combine quantities and notes if items are the same
                foreach ($pr_items as $value) {
                    $itemId = $value['item_id'];
                    $qty = $value['item_qty'];
                    $price = $value['price'] ?? 0;
                    $uom = $value['uom'] ?? null;
                    $taxId = $value['tax_id'] ?? null;
                    $notes = $value['item_notes'] ?? '';

                    if (isset($combinedItems[$itemId])) {
                        // If item already exists, increase the quantity and concatenate the notes
                        $combinedItems[$itemId]['qty'] += $qty;
                        $combinedItems[$itemId]['notes'] .= "\n" . $notes;
                    } else {
                        // Add new item to the combined list
                        $combinedItems[$itemId] = [
                            'product_id' => $itemId,
                            'qty' => $qty,
                            'price' => $price,
                            'uom' => $uom,
                            'tax_id' => $taxId,
                            'notes' => $notes,
                            'qty_diterima' => 0,
                            'is_master' => 1,
                        ];
                    }
                }
            }

            // Insert combined items into PurchaseOrderItem
            foreach ($combinedItems as $item) {
                $order->items()->create($item);
            }

            if ($request->status == 5) {
                createNotification(
                    'POCP200',
                    [
                        'user_id' => auth()->user()->id
                    ],
                    [
                        'user' => auth()->user()->name,
                    ],
                    ['brand_id' => 1]
                );
                createNotification(
                    'PORA200',
                    [],
                    [
                        'user_created' => auth()->user()->name,
                        'po_number' => $order->po_number,
                    ],
                    ['brand_id' => 1]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]purchase_order',
                'log_description' => 'Create Bulk Purchase Order - ' . $order->po_number,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Purchase Order Bulk Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Purchase Order Bulk Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
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

    public function rejectPurchaseOrder(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status'  => 6,
                'rejected_reason'  => $request->reject_reason,
                'rejected_by'  => auth()->user()->id,
            ];
            $row = PurchaseOrder::find($purchase_order_id);
            $row->update($data);

            createNotification(
                'POA200',
                [
                    'user_id' => $row->user_created
                ],
                [
                    'user' => $row->created_by_name,
                    'user_rejected' => $row->rejected_by_name,
                    'po_number' => $row->po_number,
                    'rejected_reason'  => $request->reject_reason,
                ],
                ['brand_id' => 1]
            );

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Gagal Diupdate'
            ], 400);
        }
    }

    public function approvePurchaseOrder(Request $request, $purchase_order_id)
    {
        $row = PurchaseOrder::find($purchase_order_id);

        $productItems = [];

        foreach ($row->items as $key => $value) {
            $productItems[] = [
                "product_code" => $this->convertSku($value->product->sku),
                "quantity" => $value->qty,
                "unit_price" => $value->price
            ];
        }

        if ($row->has_barcode == '1' && $row->type_po == 'product' && $row->hit_barcode == '') {
            $barcodeResult = $this->sendToBarcodeAPI($row, $productItems);

            if ($barcodeResult !== 'Success') {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => $barcodeResult
                ], 400);
            }
        }

        try {
            DB::beginTransaction();
            $data = [
                'status'  => 1,
                'approved_by'  => auth()->user()->id,
                'stock_opname' => $request->stock_opname ? 1 : 0
            ];

            $row->update($data);



            $company = CompanyAccount::find($row->company_id, ['account_code']);
            $log = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => 'submit-po-ethix',
                'vat' => 0,
                'tax' => 0,
                'ref_id' => $row->id,
                'company_id' => $company->account_code
            ]);

            try {
                $po_date = date('Y-m-d', strtotime($row->created_at));
                // Send Data To Ethix
                $headers = [
                    'secretcode: ' . getSetting('ETHIX_SECRETCODE_' . $company->account_code),
                    'secretkey: ' . getSetting('ETHIX_SECRETKEY_' . $company->account_code),
                    'Content-Type: application/json'
                ];

                $body = array(
                    "client_code" => getSetting('ETHIX_CLIENTCODE_' . $company->account_code),
                    "location_code" => $row->warehouse->ethix_id,
                    "purchase_code" => $row->po_number,
                    "purchase_type" => "PO",
                    "production_batch" => 0,
                    "delivery_number" => $this->generateDONumber($row->id),
                    "purchase_date" => $po_date,
                    "est_arrival" => $po_date . ' 00:00:00', //ini belum fix
                    "supplier_name" => $row->vendor_name,
                    "remarks" => "Purchase Remarks",
                    "product_information" => $productItems
                );

                setSetting('ethix_po_success_body', json_encode($body));

                $url = 'https://wms.ethix.id/index.php?r=Externalv2/Purchase/PostPurchase';
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $url);
                curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_TIMEOUT, 9000);
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($body));
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
                $responseData = curl_exec($handle);
                curl_close($handle);
                setSetting('ethix_po_success', json_encode($responseData));
                $responseJSON = json_decode($responseData, true);
                if (!$responseJSON && is_string($responseData)) {
                    $row->update(['status_ethix_submit' => 'needsubmited']);
                    $log->update(['body' => json_encode($body)]);
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $log->id,
                        'order_id' => $row->id
                    ], [
                        'order_submit_log_id' => $log->id,
                        'order_id' => $row->id,
                        'status' => 'failed',
                        'error_message' => $responseData
                    ]);
                    return;
                }

                // Check if any error occured
                if (curl_errno($handle)) {
                    $log->update(['body' => json_encode($body)]);
                    $row->update(['status_ethix_submit' => 'needsubmited']);
                    return;
                }


                if (isset($responseJSON['status'])) {
                    if (in_array($responseJSON['status'], [200, 201])) {
                        $row->update(['status_ethix_submit' => 'submited']);
                        $log->update(['body' => null]);
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $log->id,
                            'order_id' => $row->id
                        ], [
                            'order_submit_log_id' => $log->id,
                            'order_id' => $row->id,
                            'status' => 'success',
                            'error_message' => 'SUCCESS SUBMIT ETHIX'
                        ]);
                    } else {
                        $row->update(['status_ethix_submit' => 'needsubmited']);
                        $log->update(['body' => json_encode($body)]);
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $log->id,
                            'order_id' => $row->id
                        ], [
                            'order_submit_log_id' => $log->id,
                            'order_id' => $row->id,
                            'status' => 'failed',
                            'error_message' => $responseJSON['message']
                        ]);
                    }
                }
            } catch (\Throwable $th) {
                //throw $th;
                setSetting('ethix_po_error', $th->getMessage());
            }

            createNotification(
                'POA200',
                [
                    'user_id' => $row->user_created
                ],
                [
                    'user' => $row->created_by_name,
                    'user_approved' => $row->approved_by_name,
                    'po_number' => $row->po_number,
                ],
                ['brand_id' => 1]
            );

            createNotification(
                'PORP200',
                [],
                [
                    'po_number' => $row->po_number,
                ],
                ['brand_id' => 1]
            );

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Approve Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    private function sendToBarcodeAPI($row, $productItems)
    {
        $apiUrl = 'https://brcd-testing.flimty.co/api/barcode/purchase';
        $headers = [
            'x-api-key: ' . getSetting('BARCODE_TOKEN'),
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
            'products' => $products,
            'created_by' => auth()->user()->id
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
                'status' => (@$response->order_id != '-1' || @$response->success != false) ? 'Success' : 'Failed',
                'description' => $response->msg ?? '-'
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

    public function assignToWarehouse(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status'  => $request->status
            ];
            $row = PurchaseOrder::find($purchase_order_id);
            $row->update($data);

            if ($request->stock_opname) {
                foreach ($row->items as $key => $value) {
                    PurchaseOrderStockOpname::updateOrCreate(['purchase_order_id' => $row->id], [
                        'purchase_order_id' => $row->id,
                        'product_id' => $value->product_id,
                        'stock_opname_date' => date('Y-m-d'),
                        'stock_opname_qty' => $value->qty,
                    ]);
                }
            }

            createNotification(
                'PORP200',
                [],
                [
                    'po_number' => $row->po_number,
                ],
                ['brand_id' => 1]
            );

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Gagal Diupdate'
            ], 400);
        }
    }

    public function addProductItem(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseOrder::find($purchase_order_id);
            $brand_id = $row->brand_id;
            $uid_inventory = hash('crc32', Carbon::now()->format('U'));
            $received_number = $this->generateReceiveNumber($purchase_order_id);
            if ($row->items()->where('status', 1)->count() == 0) {
                $row->update(['status' => 2, 'received_by' => auth()->user()->id]);
                PurchaseOrderItem::updateOrCreate([
                    'purchase_order_id' => $purchase_order_id,
                    'product_id' => $request->product_id,
                ], [
                    'qty_diterima' => $request->qty_diterima,
                    'status' => 1,
                    'notes' => $request->notes ?? null,
                    'received_number' => $received_number,
                    'do_number' => $this->generateDONumber($row->id),
                    'ref' => $uid_inventory,
                    'received_date' => Carbon::now(),
                ]);
            } else {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchase_order_id,
                    'product_id' => $request->product_id,
                    'qty' => $request->qty,
                    'price' => $request->prices,
                    'tax_id' => $request->tax_id,
                    'uom' => $request->uom,
                    'received_number' => $received_number,
                    'do_number' => $this->generateDONumber($row->id),
                    'qty_diterima' => $request->qty_diterima,
                    'status' => 1,
                    'notes' => $request->notes ?? null,
                    'ref' => $uid_inventory,
                    'received_date' => Carbon::now(),
                ]);
            }

            $data_inventory = [
                'reference_number'  => $row->po_number,
                'warehouse_id'  => $row->warehouse_id,
                'created_by'  => $row->created_by,
                'vendor'  => $row->vendor_code,
                'status'  => 'done',
                'received_date'  => date('Y-m-d'),
                'received_by'  => auth()->user()->id,
                'note'  => 'Penerimaan Barang dari Purchase Order',
                'company_id'  => $row->company_id,
            ];


            $data_inventory['uid_inventory'] = $uid_inventory;
            $inventory = InventoryProductStock::create($data_inventory);
            if ($request->type == 'Perlengkapan') {
                // Generate barcodes and save to Asset model
                $barcodes = [];
                $qty = $request->qty_diterima;
                for ($i = 0; $i < $qty; $i++) {
                    $barcode = strtoupper(substr(str_shuffle(str_repeat('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 8)), 0, 8));
                    $barcodes[] = $barcode;
                    Asset::create([
                        'purchase_order_id' => $purchase_order_id,
                        'brand_id' => $brand_id,
                        'product_id' => $request->product_id,
                        'barcode' => $barcode,
                        'item_name' => $request->product_name,
                        'generate_date' => date('Y-m-d'),
                        'exp_date' => date('Y-m-d'),  // Example expiration date, adjust as needed
                        'company_id' => @$row->company_id,
                    ]);
                }
                $po = PurchaseOrder::find($purchase_order_id);
                createNotification(
                    'PR204',
                    [],
                    [
                        'pr_number' => $po->po_number,
                        'generate_date' => date('d-m-Y'),
                        'generate_user' => auth()->user()->name,
                    ],
                    []
                );
            }

            // stock
            $ref = md5($purchase_order_id . '_' . $request->product_id . '_' . $row->po_number);
            if ($row->type_po == 'product') {
                $data_inventory_item = [
                    'uid_inventory'  => $inventory->uid_inventory,
                    'product_id'  => $request->product_id,
                    'qty'  => $request->qty_diterima,
                    'price'  => $request->price,
                    'subtotal'  => $request->subtotal,
                    'type'  => 'stock',
                    'received_number' => $received_number,
                    'ref' => $ref
                ];
                InventoryItem::create($data_inventory_item);
            }


            createNotification(
                'PORPA200',
                [],
                [
                    'po_number' => $row->po_number,
                ],
                ['brand_id' => 1]
            );

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateProductItem(Request $request, $purchase_order_items_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'qty_diterima' => $request->qty_diterima,
                'notes' => $request->notes ?? null,
            ];
            $row = PurchaseOrderItem::find($purchase_order_items_id);

            $dari = "[qty_diterima ($row->qty_diterima => $request->qty_diterima)] \n";
            $dari .= "[notes ($row->notes => $request->notes)] \n";
            $dataLog = [
                'log_type' => '[fis-dev]updateProductItem',
                'log_description' => 'Update Product Purchase Item - ' . $dari,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteProductItem(Request $request, $purchase_order_items_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseOrderItem::find($purchase_order_items_id);
            $purchase_order_item = PurchaseOrderItem::where('purchase_order_id', $row->purchase_order_id)->whereStatus(1)->count();
            $po = PurchaseOrder::where('id', $row->purchase_order_id)->first();
            // $po->update(['status' => 1]);
            if ($purchase_order_item == 1) {
                if ($po->type_po == 'product') {
                    $ref = $purchase_order_item->ref;
                    InventoryItem::whereRef($ref)->delete();
                    ProductStock::whereRef($ref)->delete();
                }
            } else {
                if ($po->type_po == 'product') {
                    $ref = $row->ref;
                    $inventory = InventoryProductStock::where('uid_inventory', $row->ref)->first();
                    if ($inventory) {
                        $inventory->update([
                            'status' => 'ready',
                            'inventory_status' => 'canceled',
                        ]);
                    }
                    ProductStock::whereRef($ref)->delete();
                }
            }

            $row->delete();
            $dataLog = [
                'log_type' => '[fis-dev]deleteProductItem',
                'log_description' => 'Delete Product Purchase Item - ' . $row->ref,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan',
                'msg' => $th->getMessage()
            ], 400);
        }
    }

    public function invoiceProductItem(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->invoice_entry == 1) {
                $due_date = Carbon::now()->addDays($request->item_id[0]);
                $data['vendor_doc_number'] = $request->vendor_doc_number;
                $data['due_date'] = $due_date;
                $data['invoice_entry'] = 1;
                $data['invoice_date'] = $request->invoice_date ?? Carbon::now();

                PurchaseOrderItem::where('uid_invoice', $request->uid_invoice)->update($data);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Berhasil Disimpan'
                ]);
            }

            $uid_invoice = hash('crc32', Carbon::now()->format('U'));
            foreach ($request->item_id as $key => $value) {

                $row = PurchaseOrderItem::find($value);
                $data = ['invoice_entry' => $request->invoice_entry];
                // insert invoice entry
                if ($request->invoice_entry == 2) {
                    $data['confirm_by'] = auth()->user()->id;
                    $data['invoice_date'] = $request->invoice_date ?? Carbon::now();
                    $data['uid_invoice'] = $uid_invoice;
                }

                // invoiced
                if ($request->invoice_entry == 1) {
                    $due_date = Carbon::now()->addDays($row->purchaseOrder->term_days);
                    $data['vendor_doc_number'] = $request->vendor_doc_number;
                    $data['due_date'] = $due_date;
                }

                // cancel invoice
                if ($request->invoice_entry == 0) {
                    $data['uid_invoice'] = null;
                    $data['invoice_date'] = null;
                }

                $row->update($data);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan'
            ], 400);
        }
    }

    public function updateStatusPurchaseOrder(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status'  => $request->status
            ];
            if ($request->status == 4) {
                $data['received_by'] = auth()->user()->id;
            }
            $row = PurchaseOrder::find($purchase_order_id);
            $row->update($data);

            if ($request->status == 4) {
                // update inventory
                $uid_inventory = hash('crc32', Carbon::now()->format('U'));
                $data_inventory = [
                    'uid_inventory'  => $uid_inventory,
                    'reference_number'  => $row->po_number,
                    'warehouse_id'  => $row->warehouse_id,
                    'created_by'  => $row->created_by,
                    'vendor'  => $row->vendor_code,
                    'status'  => 'done',
                    'received_date'  => date('Y-m-d'),
                    'received_by'  => auth()->user()->id,
                    'note'  => 'Penerimaan Barang dari Purchase Order',
                    'company_id'  => $row->company_id,
                ];

                $inventory = InventoryProductStock::create($data_inventory);
                foreach ($row->items as $key => $value) {
                    $price = $value->product->getPrice('member')['final_price'];
                    $data = [
                        'uid_inventory'  => $inventory->uid_inventory,
                        'product_id'  => $value->product_id,
                        'qty'  => $value->qty_diterima,
                        'price'  => $price,
                        'subtotal'  => $value->subtotal,
                        'type'  => 'stock',
                    ];
                    InventoryItem::create($data);
                }

                foreach ($row->items as $key => $value) {
                    $product = ProductVariant::find($value->product_id);
                    $data_stock = [
                        'uid_inventory'  => $inventory->uid_inventory,
                        'warehouse_id'  => $row->warehouse_id,
                        'product_id'  => $product ? $product->product_id : $value->product_id,
                        'product_variant_id'  => $value->product_id,
                        'stock'  => $value->qty_diterima,
                        'company_id'  => $row->company_id,
                    ];
                    ProductStock::create($data_stock);
                }

                createNotification(
                    'PORPA200',
                    [],
                    [
                        'po_number' => $row->po_number,
                    ],
                    ['brand_id' => 1]
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Gagal Diupdate'
            ], 400);
        }
    }

    public function purchaseOrderComplete(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status'  => 7
            ];
            $row = PurchaseOrder::find($purchase_order_id);
            $row->update($data);

            createNotification(
                'PO200',
                [
                    'user_id' => $row->created_by,
                ],
                [
                    'po_number' => $row->po_number,
                    'user' => $row->created_by_name,
                ],
                ['brand_id' => 1]
            );
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Status Gagal Diupdate'
            ], 400);
        }
    }

    public function cancelPurchaseOrder($purchase_order_id)
    {
        $order = PurchaseOrder::find($purchase_order_id);
        $order->update(['status' => 8]);
        return response()->json([
            'status' => 'success',
            'message' => 'Data Purchase Order berhasil dihapus'
        ]);
    }

    // generate po number auto increment with format PO-0001
    public function generatePoNumber()
    {
        $lastPo = PurchaseOrder::whereNotNull('po_number')->orderBy('id', 'desc')->first();
        $number = '00001';
        if ($lastPo) {
            $number = substr($lastPo->po_number, -4);
            $number = (int) $number + 1;
            $number = sprintf("%04d", ((int)$number));
        }
        return 'PO-' . date('d') . '-' . $number;
    }

    // generate receive number auto increment with format PO-0001
    public function generateReceiveNumber($purchase_order_id = null)
    {
        $username = rand(232, 987);
        $lastPo = PurchaseOrderItem::whereNotNull('received_number')->orderBy('id', 'desc')->first();
        if ($lastPo) {
            $number = substr($lastPo->received_number, -4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        } else {
            $number = '0001';
        }
        return 'RCV/' . $username . '/' . date('Y') . '/' . $number;
    }

    public function generateDONumber($purchase_order_id = null)
    {
        $username = rand(232, 987);
        $lastPo = PurchaseOrderItem::whereNotNull('do_number')->orderBy('id', 'desc')->first();
        if ($lastPo) {
            $number = substr($lastPo->do_number, -4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        } else {
            $number = '0001';
        }
        return 'DO/' . $username . '/' . date('Y') . '/' . $number;
    }

    // save billing
    public function billingSave(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'purchase_order_id' => $request->purchase_order_id,
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
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Billing Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Disimpan',
            ]);
        }
    }

    // billing approve
    public function billingApprove(Request $request, $purchase_billing_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status' => 1,
                'approved_by' => auth()->user()->id,
            ];
            $row = PurchaseBilling::find($purchase_billing_id);
            $row->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Billing Berhasil Diapprove',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Diapprove',
            ]);
        }
    }

    // billing reject
    public function billingReject(Request $request, $purchase_billing_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status' => 2,
                'rejected_by' => auth()->user()->id,
            ];
            $row = PurchaseBilling::find($purchase_billing_id);
            $row->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Billing Berhasil Direject',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Direject',
            ]);
        }
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

    public function updateDoNumber(Request $request, $purchase_order_id)
    {
        try {
            DB::beginTransaction();
            $row = PurchaseOrderItem::where('purchase_order_id', $purchase_order_id)->where('do_number', $request->do_number_exist)->first();
            if ($row) {
                $row->update(['do_number' => $request->do_number]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Do Number  nnn Berhasil Diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Do Number vvv Gagal Diupdate',
            ]);
        }
    }

    public function submitGp(Request $request)
    {
        $purchaseItems = PurchaseOrderItem::whereIn('id', $request->items)->get();

        $header = [];
        $line = [];
        foreach ($purchaseItems as $key => $item) {
            $header[] = [
                "DO_NUMBER" => $item->do_number,
                "VENDOR_CODE" => $item->purchaseOrder->vendor_code,
                "VENDOR_NAME" => $item->purchaseOrder->vendor_name
            ];

            $line[] = [
                "PURCHASE_ORDER_ID" => $item->purchaseOrder->order_number,
                "PRODUCT_ID" => $item->sku,
                "UOM" => $item->u_of_m,
                "PRICE" => $item->price,
                "QTY" => $item->qty
            ];
        }

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => getSetting('GP_URL') . '/Receiving/ReceivingEntry',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>  json_encode([
                    'header' => $header,
                    'line' => $line,
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . getSetting('GP_TOKEN')
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $responseJSON = json_decode($response, true);
            // check is string
            if (!$responseJSON && is_string($response)) {
                setSetting('GP_RESPONSE_ERROR_ENTRY_1', $response);
                return response()->json([
                    'data' => null,
                    'message' => 'Error',
                ], 400);
            }

            // Check if any error occured
            if (curl_errno($curl)) {
                setSetting('GP_RESPONSE_ERROR_ENTRY_2', curl_error($curl));
                return response()->json([
                    'data' => null,
                    'message' => 'Error',
                ], 400);
            }

            setSetting('GP_RESPONSE_ENTRY_1', json_encode($responseJSON));
            if (isset($responseJSON['code'])) {
                if (in_array($responseJSON['code'], [200, 201])) {
                    foreach ($purchaseItems as $key => $item) {
                        $item->update(['status_gp', true]);
                    }
                    return response()->json([
                        'data' => $responseJSON,
                        'message' => 'Success',
                    ]);
                }
            }

            if (isset($responseJSON['desc'])) {
                setSetting('GP_RESPONSE_ERROR_ENTRY_3', $responseJSON['desc']);
                return response()->json([
                    'data' => null,
                    'message' => 'Error',
                ], 400);
            }
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            setSetting('GP_RESPONSE_ERROR_ENTRY_4', $responseBodyAsString);
            return response()->json([
                'data' => null,
                'message' => 'Error',
            ], 400);
        }
    }

    public function convertSku($curentSku)
    {
        $skus  = [
            '19996230523' => '8997230500863',
            '19996230582' => '8997230500344',
            // '8997230500924' => '8996293052050',
            // '8997230500917' => '8997230500917',
            // '6293512' => '6293512',
            // '8996293283120' => '89962932831201',
            // 'S0001' => 'S0001',
            // 'S0007' => 'fl100024',
            // '8996293218449' => '8996293218449',
            // '8997236237312' => '8997236237312',
            // '8997236236834' => '8997236236834',
            // '8997236237077' => '8997236237077',
            // '8997236237084' => '8997236237084',
            // '8996293283120' => '8996293283126',
        ];

        if ($curentSku) {
            if (isset($skus[$curentSku])) {
                return $skus[$curentSku];
            }
            return $curentSku;
        }

        // return null;
        return $curentSku;
    }
}
