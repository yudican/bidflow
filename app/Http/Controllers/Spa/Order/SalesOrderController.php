<?php

namespace App\Http\Controllers\Spa\Order;

use App\Exports\OrderManualExport;
use App\Http\Controllers\Controller;
use App\Jobs\ImportSalesOrderQueue;
use App\Jobs\ImportSalesOrderUploadQueue;
use App\Jobs\UpdatePriceQueue;
use App\Models\AddressUser;
use App\Models\Kecamatan;
use App\Models\MasterDiscount;
use App\Models\OrderManual;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\ProductNeed;
use App\Models\Role;
use App\Models\ShippingType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel; 
use Str;

class SalesOrderController extends Controller
{
    public function listSalesOrder(Request $request)
    {
        // echo"<pre>";print_r($request->all());die();
        $user = auth()->user();
        $role = $user->role->role_type;

        $query = DB::table('order_manuals as om');
        $query->join('users as u', 'u.id', '=', 'om.contact');
        $query->join('users as su', 'su.id', '=', 'om.sales');
        $query->join('users as cu', 'cu.id', '=', 'om.user_created');
        $query->join('role_user as ru', 'ru.user_id', '=', 'om.contact');
        $query->join('roles as r', 'r.id', '=', 'ru.role_id');
        $query->join('payment_terms as pt', 'pt.id', '=', 'om.payment_term');
        $query->leftJoin('logistics as lg', 'lg.id', '=', 'om.shipping_method_id');

        if ($request->isDelivery) {
            $query->join('order_deliveries as od', 'od.uid_lead', '=', 'om.uid_lead');
            $query->where('od.status', '!=', 'cancel');
        }

        // Apply search filter if provided
        if ($search = $request->input('search')) {
            $query->where(function ($subquery) use ($search) {
                $subquery->where('om.order_number', 'like', "%$search%")
                    ->orWhere('u.name', 'like', "%$search%")
                    ->orWhere('su.name', 'like', "%$search%")
                    ->orWhere('cu.name', 'like', "%$search%")
                    ->orWhere('pt.name', 'like', "%$search%");
            });
        }

        // Apply other filters based on the request
        if ($status = $request->input('status')) {
            if (is_array($status)) {
                $query->whereIn('om.status', $status);
            } else {
                $newStatus = str_replace('10', '0', $status);
                $query->whereIn('om.status', explode(',', $newStatus));
            }
        } else {
            $query->where('om.status', '>=', 0);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('om.company_id', $accountId);
        }

        // Convert created_at date from dd-mm-yyyy to yyyy-mm-dd
        $createdAt = $request->input('created_at');
        if ($createdAt) {
            $createdAt = explode(',', $createdAt);
            $createdAt = array_map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            }, $createdAt);
            // Jika tanggal awal dan akhir sama, gunakan whereDate
            if ($createdAt[0] == $createdAt[1]) {
                $query->whereDate('om.created_at', $createdAt[0]);
            } else {
                // Tambahkan waktu akhir hari untuk tanggal akhir
                $query->whereBetween('om.created_at', [$createdAt[0], $createdAt[1] . ' 23:59:59']);
            }
        }



        if ($paymentTerm = $request->input('payment_term')) {
            if (is_array($paymentTerm)) {
                $query->whereIn('om.payment_term', $paymentTerm);
            } else {
                $query->whereIn('om.payment_term', explode(',', $paymentTerm));
            }
        }

        if ($contact = $request->input('contact')) {
            $query->where('om.contact', $contact);
        }

        if ($salesChannel = $request->input('sales_channel')) {
            $query->where('u.sales_channel', 'like', "%$salesChannel%");
        }

        if ($sales = $request->input('sales')) {
            $query->where('om.sales', $sales);
        }
        if ($user_created = $request->input('user_created')) {
            $query->where('om.user_created', $user_created);
        }

        if ($printStatus = $request->input('print_status')) {
            $query->where('om.print_status', $printStatus);
        }

        if ($resiStatus = $request->input('resi_status')) {
            $query->where('om.resi_status', $resiStatus);
        }

        if ($type = $request->input('type')) {
            $query->where('om.type', $type);
        }

        if ($role == 'sales') {
            $query->where(function ($subquery) use ($user) {
                $subquery->where('om.user_created', $user->id)
                    ->orWhere('om.sales', $user->id);
            });
        }

        if ($orderType = $request->input('order_type')) {
            $query->where('om.order_type', $orderType);
        }

        // Apply ordering and pagination
        $orderLeads = $query->whereNotNull('om.contact')->whereNotNull('om.sales')->orderBy('om.status', 'asc')
            ->select(
                'om.id',
                'om.created_at',
                'om.status',
                'om.status_submit',
                'om.print_status',
                'om.resi_status',
                'om.gp_si_number',
                'om.order_number',
                'om.invoice_number',
                'om.uid_lead',
                'om.contact',
                'om.sales',
                'om.payment_term',
                'om.company_id',
                'om.user_created',
                'om.type',
                'u.name as contact_name',
                'u.email as contact_email',
                'u.telepon as contact_telepon',
                'r.role_name as role_name',
                'su.name as sales_name',
                'cu.name as created_by_name',
                'u.sales_channel as sales_channel',
                'pt.name as payment_term_name',
                'lg.logistic_name as shipping_method_name',
                'om.subtotal',
                'om.dpp',
                'om.ppn',
                'om.total',
                'om.order_type'
            )
            ->orderBy('om.created_at', 'desc')
            ->groupBy(
                'om.id',
                'om.created_at',
                'om.status',
                'om.status_submit',
                'om.print_status',
                'om.resi_status',
                'om.gp_si_number',
                'om.order_number',
                'om.invoice_number',
                'om.uid_lead',
                'om.contact',
                'om.sales',
                'om.payment_term',
                'om.company_id',
                'om.user_created',
                'om.type',
                'u.name',
                'r.role_name',
                'su.name',
                'cu.name',
                'pt.name',
                // 'ca.account_name'
            )
            ->paginate($request->input('perpage', 10));

        return response()->json([
            'status' => 'success',
            'data' => tap($orderLeads, function ($order) {
                return $order->getCollection()->transform(function ($item) {
                    // return $item;
                    $orderDeliverys = [];
                    $product_needs = [];
                    return [
                        'id' => $item->id,
                        'uid_lead' => $item->uid_lead,
                        'order_number' => $item->order_number,
                        'contact_name' => $item->contact_name,
                        'contact_email' => $item->contact_email,
                        'contact_telepon' => $item->contact_telepon,
                        'role_name' => $item->role_name,
                        'sales_name' => $item->sales_name,
                        'created_by_name' => $item->created_by_name,
                        'created_at' => $item->created_at,
                        'type' => $item->type,
                        'amount' => $item->total,
                        'total' => $item->total,
                        'subtotal' => $item->subtotal,
                        'payment_term_name' => $item->payment_term_name,
                        'shipping_method_name' => $item->shipping_method_name,
                        'status' => $item->status,
                        'status_submit' => $item->status_submit,
                        'print_status' => $item->print_status,
                        'resi_status' => $item->resi_status,
                        'gp_si_number' => $item->gp_si_number,
                        'product_needs' => $product_needs ?? [],
                        'order_delivery' => $orderDeliverys,
                        'billings' =>  [],
                        'order_type' => $item->order_type
                    ];
                });
            }),
        ]);
    }


    public function loadSalesOrderInvoice(Request $request)
    {
        $search = $request->search;
        $invoice_date = $request->invoice_date;
        $status = $request->status;
        $account_id = $request->account_id ?? auth()->user()->company_id;
        $orderLead =  DB::table('order_deliveries as od')
            ->leftJoin('product_needs as pn', 'od.product_need_id', '=', 'pn.id')
            ->leftJoin('order_manuals as om', 'od.uid_lead', '=', 'om.uid_lead')
            ->leftJoin('users as u', 'u.id', '=', 'om.contact')
            ->leftJoin('users as su', 'su.id', '=', 'om.sales')
            ->leftJoin('users as cu', 'cu.id', '=', 'om.user_created')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'om.payment_term');


        if ($search) {
            $orderLead->where(function ($query) use ($search) {
                $query->where('od.invoice_number', 'like', "%$search%");
                $query->orWhere('od.no_faktur', 'like', "%$search%");
                $query->orWhere('od.delivery_number', 'like', "%$search%");
                $query->orWhere('od.gp_submit_number', 'like', "%$search%");
            });
        }

        if ($status) {
            $orderLead->whereIn('od.status', explode(',', $status));
        }

        if ($invoice_date) {
            $orderLead->whereBetween('od.invoice_date', explode(',', $invoice_date));
        }

        // cek switch account
        if ($account_id) {
            $orderLead->where('om.company_id', $account_id);
        }

        $orderLeads = $orderLead->orderBy('od.created_at', 'desc')
            // ->where('od.is_invoice', 1)
            ->select(
                'od.uid_lead',
                'od.uid_invoice',
                'od.is_invoice',
                'od.uid_invoice as uid_delivery',
                'od.gp_submit_number',
                'od.submit_klikpajak',
                'od.product_need_id',
                'od.invoice_number',
                'od.no_faktur',
                'od.delivery_number',
                'od.status',
                'od.invoice_date',
                'od.created_at',
                'od.type_so',
                'od.due_date',
                'om.company_id',
                'u.name as contact_name',
                'su.name as sales_name',
                'pt.name as payment_term_name',
                'cu.name as created_by_name',
                'om.subtotal',
                'om.diskon',
                'om.dpp',
                'om.ppn',
                'om.ongkir',
                'om.kode_unik',
                'om.total',
            )
            ->groupBy(
                'od.uid_lead',

            )
            ->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $orderLeads,
        ]);
    }

    public function detailSalesOrder($uid_lead)
    {
        // Fetch the main sales order details
        $order = DB::table('order_manuals as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.contact')
            ->leftJoin('users as su', 'su.id', '=', 'a.sales')
            ->leftJoin('users as cu', 'cu.id', '=', 'a.user_created')
            ->leftJoin('users as c', 'c.id', '=', 'a.courier')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'a.payment_term')
            ->leftJoin('company_accounts as ca', 'ca.id', '=', 'a.company_id')
            ->leftJoin('warehouses as w', 'w.id', '=', 'a.warehouse_id')
            ->leftJoin('master_bins as bin', 'a.master_bin_id', '=', 'bin.id')
            ->select(
                'a.*',
                'a.total as total_amount',
                'a.diskon as discount_amount',
                'u.name as contact_name',
                'su.name as sales_name',
                'cu.name as created_by_name',
                'u.sales_channel as sales_channel',
                'pt.name as payment_term_name',
                'c.name as courier_name',
                'w.name as warehouse_name',
                'bin.name as master_bin_name',
                'ca.account_name as company_name',
            )
            ->where('a.uid_lead', $uid_lead)
            ->first();

        if ($order) {
            // Fetch the related product needs
            // $product_needs = DB::table('vw_sales_orders_items as b')
            //     ->where('b.uid_lead', $uid_lead)
            //     ->get();
            // $order_delivery = DB::table('vw_sales_orders_delivery_items as c')
            //     ->where('c.uid_lead', $uid_lead)
            //     ->get();

            // Combine them into the desired output
            $order->product_needs = [];
            $order->order_delivery = [];
            $order->ethix_items = [];
            $order->billings = [];
        }

        if ($order->total == 0) {
            if ($order->type != 'freebies') {
                UpdatePriceQueue::dispatch($order)->onQueue('queue-backend');
            }
        }

        // $order = DB::table('order_manuals')->select([
        //     'order_manuals.*',
        //     'users.name AS contact_name',
        //     'sales_user.name AS sales_name',
        //     'created_user.name AS created_by_name',
        //     'courier_user.name AS courier_name',
        //     'payment_terms.name AS payment_term_name',
        //     'company_accounts.account_name AS company_name',
        //     'warehouses.name AS warehouse_name',
        // ])
        //     ->join('users', function ($join) {
        //         $join->on('users.id', '=', 'order_manuals.contact');
        //     })
        //     ->join('users AS sales_user', function ($join) {
        //         $join->on('sales_user.id', '=', 'order_manuals.sales');
        //     })
        //     ->join('users AS created_user', function ($join) {
        //         $join->on('created_user.id', '=', 'order_manuals.user_created');
        //     })
        //     ->leftJoin('users AS courier_user', function ($join) {
        //         $join->on('courier_user.id', '=', 'order_manuals.courier');
        //     })
        //     ->leftJoin('payment_terms', function ($join) {
        //         $join->on('order_manuals.payment_term', '=', 'payment_terms.id');
        //     })
        //     ->leftJoin('company_accounts', function ($join) {
        //         $join->on('order_manuals.company_id', '=', 'company_accounts.id');
        //     })
        //     ->leftJoin('warehouses', function ($join) {
        //         $join->on('order_manuals.warehouse_id', '=', 'warehouses.id');
        //     })
        //     ->where('uid_lead', $uid_lead)->first();

        return response()->json([
            'data' => $order,
            'print' => [
                'si' => route('print.si', $uid_lead),
                'so' => route('print.so', $uid_lead),
                'sj' => route('print.sj', $uid_lead),
            ],
            'message' => 'success'
        ]);
    }

    public function detailSalesOrderItems($uid_lead)
    {
        $orderItems = DB::table('vw_sales_orders_items')->where('uid_lead', $uid_lead)->get();
        // $orderItems = DB::table('product_needs')
        //     ->select(
        //         'product_needs.*',
        //         'master_tax.tax_code',
        //         'master_tax.tax_percentage',
        //         'master_discounts.percentage as discount_percentage',
        //         'prices.final_price',
        //         'product_variants.name as product_name',
        //         'product_variants.sku as product_sku',
        //         'product_variant_stocks.stock_of_market as stock'
        //     )
        //     ->leftJoin('order_manuals', 'product_needs.uid_lead', '=', 'order_manuals.uid_lead')
        //     ->leftJoin('product_variants', 'product_needs.product_id', '=', 'product_variants.id')
        //     ->leftJoin('product_variant_stocks', function ($join) {
        //         $join->on('product_variants.id', '=', 'product_variant_stocks.product_variant_id')
        //             ->on('product_variant_stocks.warehouse_id', '=', 'order_manuals.warehouse_id');
        //     })
        //     ->leftJoin('prices', 'product_variants.id', '=', 'prices.product_variant_id')
        //     ->leftJoin('master_tax', 'product_needs.tax_id', '=', 'master_tax.id')
        //     ->leftJoin('master_discounts', 'product_needs.discount_id', '=', 'master_discounts.id')
        //     ->where('product_needs.uid_lead', $uid_lead)
        //     ->groupBy('product_needs.uid_lead')
        //     ->get();


        return response()->json([
            'data' => $orderItems,
            'message' => 'success'
        ]);
    }


    public function detailSalesOrderDeliveryItems($uid_lead)
    {
        // $orderItems = DB::table('vw_sales_orders_delivery_items')->where('uid_lead', $uid_lead)->get();
        $orderItems = DB::table('order_deliveries')
            ->select(
                'order_deliveries.id',
                'product_needs.qty as qty',
                'order_deliveries.qty_delivered as qty_delivered',
                'order_deliveries.resi as resi',
                'order_deliveries.courier as courier',
                'order_deliveries.sender_name as sender_name',
                'order_deliveries.sender_phone as sender_phone',
                'order_deliveries.status as status',
                'order_deliveries.invoice_number as invoice_number',
                'order_deliveries.is_invoice as is_invoice',
                'order_deliveries.submit_klikpajak as submit_klikpajak',
                'order_deliveries.no_faktur as no_faktur',
                'order_deliveries.uid_lead as uid_lead',
                'product_variants.name as product_name',
                'product_variants.sku as product_sku'
            )
            ->leftJoin('product_needs', 'order_deliveries.product_need_id', '=', 'product_needs.id')
            ->leftJoin('product_variants', 'product_needs.product_id', '=', 'product_variants.id')
            ->leftJoin('master_tax', 'product_needs.tax_id', '=', 'master_tax.id')
            ->where('order_deliveries.uid_lead', $uid_lead)
            ->get();

        return response()->json([
            'data' => $orderItems,
            'message' => 'success'
        ]);
    }


    public function detailSalesOrderBillingsItems($uid_lead)
    {
        $orderItems = DB::table('lead_billings')
            ->select('lead_billings.*', 'users.name as approved_by_name')
            ->leftJoin('users', 'lead_billings.approved_by', 'users.id')
            ->where('lead_billings.uid_lead', $uid_lead)
            ->get();

        return response()->json([
            'data' => $orderItems,
            'message' => 'success'
        ]);
    }

    public function loadOrderItems(Request $request)
    {
        $orderItems = DB::table('order_deliveries as a')
            ->select('a.id', 'a.qty_delivered', 'a.product_need_id', 'a.invoice_number', 'a.submit_klikpajak', 'a.uid_invoice', 'a.gp_submit_number', 'a.is_invoice', 'a.uid_lead', 'product_variants.name as product_name', 'product_variants.sku as sku', 'packages.name as u_of_m')
            ->leftJoin('product_needs', 'a.product_need_id', '=', 'product_needs.id')
            ->leftJoin('product_variants', 'product_needs.product_id', '=', 'product_variants.id')
            ->leftJoin('packages', 'product_variants.package_id', '=', 'packages.id')
            ->whereIn('a.uid_lead', $request->uid_lead)
            ->whereNull('a.gp_submit_number')
            ->get();


        return response()->json([
            'data' => $orderItems,
            'message' => 'success'
        ]);
    }


    public function changeAddress(Request $request)
    {
        $order = OrderManual::find($request->uid_lead);
        if ($order) {
            $order->address_id = $request->address_id;
            $order->save();
        }

        return response()->json([
            'data' => $order,
            'message' => 'success'
        ]);
    }

    function getSalesChannel($type = 'manual', $account_id = null)
    {
        $data = [
            ['label' => 'Corner', 'value' => 'corner', 'count' => OrderManual::whereType($type)->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%corner%");
            })->count()],
            ['label' => 'MTP', 'value' => 'mtp', 'count' => OrderManual::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%mtp%");
            })->count()],
            ['label' => 'Agent Portal', 'value' => 'agent-portal', 'count' => OrderManual::whereType($type)->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%agent-portal%");
            })->count()],
            ['label' => 'Distributor', 'value' => 'distributor', 'count' => OrderManual::whereType($type)->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%distributor%");
            })->count()],
            ['label' => 'Super Agent', 'value' => 'super-agent', 'count' => OrderManual::whereType($type)->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%super-agent%");
            })->count()],
            ['label' => 'Modern Store', 'value' => 'modern-store', 'count' => OrderManual::whereType($type)->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%modern-store%");
            })->count()],
            ['label' => 'E-Store', 'value' => 'e-store', 'count' => OrderManual::whereType($type)->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'like', "%e-store%");
            })->count()],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function importOrder(Request $request)
    {
        $user = auth()->user();
        $submitLog = OrderSubmitLog::create([
            'submited_by' => $user?->id,
            'type_si' => 'import-so-' . $request->type,
            'vat' => 0,
            'tax' => 0,
            'ref_id' => null,
            'company_id' => $user->company_id
        ]);
        $file = $request->file('file');
        if (!$request->hasFile('file')) {
            return response()->json([
                'data' => [],
                'message' => 'File tidak diupload'
            ], 400);
        }


        if (!$file->isValid()) {
            return response()->json([
                'data' => [],
                'message' => 'File tidak valid'
            ], 400);
        }

        $fileUpload = $this->uploadFile($request, 'file', $submitLog->id);
        if ($submitLog) {
            try {
                DB::beginTransaction();

                $data = Excel::toArray([], $file);
                $headers = $data[0][0];

                // Ambil data setelah header
                $rows = array_slice($data[0], 1);

                // Pemetaan data berdasarkan header
                $mappedData = [];
                foreach ($rows as $row) {
                    // Check if the row is not empty and contains the required columns
                    if (!empty(array_filter($row))) {
                        // Pemetaan hanya jika Code SO (or any other critical column) exists
                        if (isset($row[1]) && !empty($row[1])) {
                            $mappedRow = [];

                            foreach ($headers as $key => $header) {
                                // Assign the row data to the corresponding header key
                                $mappedRow[$header] = $row[$key] ?? null; // Use null for missing values
                            }

                            $codeSO = $mappedRow['Code SO'];

                            // Jika Code SO sudah ada, tambahkan item ke dalam array 'items'
                            if (isset($mappedData[$codeSO])) {
                                $mappedData[$codeSO]['items'][] = $mappedRow;
                            } else {
                                // Jika Code SO belum ada, buat array baru dengan 'items' berisi item pertama
                                $mappedData[$codeSO] = array_merge($mappedRow, ['items' => [$mappedRow]]);
                            }
                        }
                    }
                }

                // Mengubah array associatif menjadi array numerik jika diperlukan
                $result = array_values($mappedData);

                removeSetting('import-so-' . $request->type . '-' . $user->id);
                removeSetting('import-so-' . $request->type . '-' . $user->id . '-progress');
                setSetting('import-so-' . $request->type . '-' . $user->id, count($result));
                // setSetting('import-so-' . $submitLog->id, json_encode($result));

                ImportSalesOrderUploadQueue::dispatch($submitLog->id, $request->type, $fileUpload)->onQueue('queue-import');
                foreach ($result as $key => $item) {
                    // Only pass necessary data
                    ImportSalesOrderQueue::dispatch($item, $request->type, $key, $user->id, $submitLog->id, $fileUpload)->onQueue('queue-import');
                }

                DB::commit();
                return response()->json([
                    'data' => [],
                    'message' => 'success'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'data' => [],
                    'message' => 'gagal import data',
                    'error' => $th->getMessage(),
                ], 400);
            }
        }

        return response()->json([
            'data' => [],
            'message' => 'gagal import data 2'
        ], 400);
    }

    public function uploadFile($request, $path = 'file', $reff_id)
    {
        if (!$request->hasFile($path)) {
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id
            ], [
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id,
                'status' => 'failed',
                'error_message' => 'File Tidak Ditemukan'
            ]);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id
            ], [
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id,
                'status' => 'failed',
                'error_message' => 'File Tidak Valid'
            ]);
        }
        // Get original filename and prepare path
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);

        // Create unique filename while preserving original name
        $uniqueFilename = $filename . '_' . time() . '.' . $extension;

        // Upload to S3 with original filename
        $uploaded = Storage::disk('s3')->putFileAs(
            $path,
            $file,
            $uniqueFilename,
            'public'
        );
        // $file = Storage::disk('s3')->put($path, $request[$path], 'public');
        return $uploaded;
    }




    public function exportOrder(Request $request)
    {
        // print_r($request->all());die();
        $file_name = 'sales-order/fis-order-' . $request->type . date('d-m-Y') . '.xlsx';
        // if ($request->created_at && is_array($request->created_at)) {
        //     $file_name = 'sales-order/fis-order-' . $request->type . '(' . implode('-', $request->created_at) . ').xlsx';
        //     if ($request->created_at[0] == $request->created_at[1]) {
        //         $file_name = 'sales-order/fis-order-' . $request->type . '(' . $request->created_at[0] . ').xlsx';
        //     }
        // }

        Excel::store(new OrderManualExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        // Excel::store(new OrderManualExport($request), $file_name, 'public');

        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            // 'data' => Storage::url($file_name),
            'message' => 'List Export'
        ]);
    }

    public function newOrderAgent(Request $request)
    {
        $order_number = OrderManual::generateOrderNumber(5);
        $invoice_number = OrderManual::generateInvoiceNumber(5);

        try {
            DB::beginTransaction();

            // check if user exist
            $user = User::find($request->user_id);
            $user_id = $request->user_id;
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'telepon' => formatPhone($request->phone),
                    'password' => Hash::make('admin123'),
                    'created_by' => auth()->user()->id,
                    'sales_channel' => 'agent-portal',
                    'uid' => $this->generateCustomerCode(),
                    'status' => 0
                ]);

                $user_id = $user->id;
                $role = Role::find('0feb7d3a-90c0-42b9-be3f-63757088cb9a');
                $user->brands()->sync($request->brand_id);
                $user->teams()->sync(1, ['role' => $role->role_type]);
                $user->roles()->sync('0feb7d3a-90c0-42b9-be3f-63757088cb9a');
            } else {
                $user->update(['status' => 1]);
            }

            $data = [
                'uid_lead' => generateUid(),
                'type_customer' => 'existing',
                'contact'  => $request->user_id,
                'sales'  => '963b12db-5dbf-4cd5-91f7-366b2123ccb9',
                'brand_id'  => $request->brand_id,
                'warehouse_id' => 2,
                'payment_term'  => $request->payment_term,
                'shipping_method_id'  => $request->shipping_method_id,
                'status'  => 0,
                'type' => 'agent',
                'company_id' => $request->company_id,
                'expired_at' =>  Carbon::now()->addDay(),
            ];

            $data['title'] = $order_number;
            $data['order_number'] = $order_number;
            $data['invoice_number'] = $invoice_number;
            $data['user_created'] = $user->id;

            if ($request->status == 4) {
                $kode_unik = $request->kode_unik ?? $this->getUniqueCodeLead();
                $data['kode_unik'] = $kode_unik;
                $data['temp_kode_unik'] = $kode_unik;
            }


            // if ($request->status == 2) {
            //     $courier = User::whereHas('roles', function ($q) {
            //         return $q->where('role_type', 'warehouse');
            //     })->first(['id']);
            //     $main = AddressUser::where('user_id', $request->contact)->where('is_default', 1)->first(['id']);
            //     if (empty($main)) {
            //         $main = AddressUser::where('user_id', $request->contact)->first(['id']);
            //     }
            //     $data['address_id'] = $main ? $main->id : null;
            //     $data['shipping_type'] = 1;
            //     $data['courier'] = $courier ? $courier->id : null;
            // }


            // create address
            $kecamatan = Kecamatan::wherePid($request->kecamatan['kec_id'])->first();

            $address = null;
            if ($kecamatan) {
                if ($request->address_id == 'new') {
                    AddressUser::where('user_id', $user_id)->update(['is_default' => 0]);
                    $kodepos = $request->kodepos ?? $request->kecamatan->kodepos;

                    $datastore = [
                        'type' => '-',
                        'nama' => $request->name,
                        'telepon' => formatPhone($request->phone),
                        'user_id' => $user_id,
                        'alamat' => $request->alamat ?? $request->alamat_detail,
                        'kelurahan_id' => $request->kecamatan['kel_id'],
                        'kecamatan_id' => $request->kecamatan['kec_id'],
                        'kabupaten_id' => $request->kecamatan['kab_id'],
                        'provinsi_id' => $request->kecamatan['prov_id'],
                        'is_default' => 1,
                    ];

                    if ($kodepos > 0 || $request->kodepos > 0) {
                        $datastore['kodepos'] = $kodepos ?? $request->kodepos;
                    }

                    $address = AddressUser::updateOrCreate([
                        'kecamatan_id' => $request->kecamatan_id,
                        'user_id' => $user_id
                    ], $datastore);
                } else {
                    $address = AddressUser::find($request->address_id);
                    if ($address) {
                        if ($address->kodepos == 0 || !$address->kodepos) {
                            $address->update(['kodepos' => $request->kodepos]);
                        }
                    }
                }
            }

            if ($request->address_id && $request->address_id != 'new') {
                $address = AddressUser::find($request->address_id);
                if ($address) {
                    if ($address->kodepos == 0 || !$address->kodepos) {
                        $address->update(['kodepos' => $request->kodepos]);
                    }
                }
            }


            if (!$address) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat membuat order, alamat tidak lengkap',
                ], 400);
            }

            $address_id =  $address ? $address->id : null;
            $data['address_id'] = $address_id;


            $order = OrderManual::create($data);

            foreach ($request->products as $key => $product) {
                $discountId = isset($product['discount_id']) ?  $product['discount_id'] : null;
                $discount = $discountId ? MasterDiscount::find($discountId) : null;
                $diskon = isset($product['diskon']) ? $product['diskon'] : 0;
                $price = $product['price']['final_price'] * $product['qty'];
                $percentage = $discount?->percentage > 0 ? $discount->percentage / 100 : 0;
                $amount_diskon = $price * $percentage;
                ProductNeed::create([
                    'uid_lead' => $order->uid_lead,
                    'price' => $price,
                    'qty' => $product['qty'],
                    'tax_id' => 1,
                    'discount' => $amount_diskon,
                    'product_id' => $product['id'],
                    'user_created' => $user->id,
                ]);
            }

            UpdatePriceQueue::dispatch($order)->onQueue('queue-backend');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membuat order',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat order',
                'error' => $th->getMessage(),
                'trace' => $th->getTrace()
            ], 400);
        }
    }

    public function updateStatus(Request $request)
    {
        if ($request->uid_lead) {
            try {
                DB::beginTransaction();
                OrderManual::where('uid_lead', $request->uid_lead)->update(['status' => $request->status]);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil proses pesanan',
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Gagal proses pesanan',
                ], 400);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Gagal proses pesanan, order tidak ditemukan',
        ], 400);
    }

    private function getUniqueCodeLead($field = 'temp_kode_unik', $prefix = null)
    {
        return rand(121, 999);
        $data = OrderManual::whereDate('created_at', date('Y-m-d'))->select($field)->orderBy('id', 'desc')->limit(1)->first();
        if ($data) {
            if ($data->$field > 0) {
                if ($data->$field == 500) {
                    $nomor = $prefix . '001';
                } else {
                    $awal = substr($data->$field, 3);
                    $next = sprintf("%03d", ((int)$awal + 1));
                    $nomor = $prefix . $next;
                }
            }
        } else {
            $nomor = $prefix . '001';
        }
        return $nomor;
    }

    // generate invoice MAG-001 auto increment
    public function generateCustomerCode()
    {
        $lastInvoice = User::orderBy('id', 'desc')->whereNotNull('uid')->where('uid', 'like', "%MAG%")->first();
        if ($lastInvoice) {
            $custNmbr = $lastInvoice ? $lastInvoice->invoice_id : 'MAG-000';
            $custNmbr = explode('-', $custNmbr);
            $custNmbr = isset($custNmbr[1]) ? (int) $custNmbr[1] : 0;
            $custNmbr = $custNmbr + 1;
            $custNmbr = str_pad($custNmbr, 3, '0', STR_PAD_LEFT);
            $custNmbr = 'MAG-' . $custNmbr;

            return $custNmbr;
        }
        return 'MAG-001';
    }
}
