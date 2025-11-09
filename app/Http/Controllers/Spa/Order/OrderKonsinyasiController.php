<?php

namespace App\Http\Controllers\Spa\Order;

use App\Exports\OrderManualExport;
use App\Imports\OrderKonsinyasiImport;
use App\Jobs\CreateLogQueue;
use App\Exports\OrderManualDetailExport;
use App\Http\Controllers\Spa\Order\OrderInvoiceController;
use App\Imports\TransferKonsinyasiImport;
use App\Jobs\SaveReminderActivity;
use App\Jobs\UpdatePriceQueue;
use App\Models\AddressUser;
use App\Models\Brand;
use App\Models\CompanyAccount;
use App\Models\LeadBilling;
use App\Models\LeadReminder;
use App\Models\MasterBinStock;
use App\Models\OrderDelivery;
use App\Models\OrderDeposit;
use App\Models\OrderManual;
use App\Models\OrderTransfer;
use App\Models\OrderProductBilling;
use App\Models\OrderShipping;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantStock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

class OrderKonsinyasiController extends OrderInvoiceController
{
    public function index($uid_lead = null)
    {
        return view('spa.spa-index');
    }

    // public function listOrderLead(Request $request)
    // {
    //     $search = $request->search;
    //     $contact = $request->contact;
    //     $sales = $request->sales;
    //     $created_at = $request->created_at;
    //     $status = $request->status;
    //     $user = auth()->user();
    //     $role = $user->role->role_type;
    //     $account_id = $request->account_id;
    //     $type = $request->type;
    //     $payment_term = $request->payment_term;
    //     $print_status = $request->print_status;
    //     $resi_status = $request->resi_status;
    //     $sales_channel = $request->sales_channel;

    //     $orderLead =  OrderManual::query();

    //     if ($search) {
    //         $orderLead->where(function ($query) use ($search) {
    //             $query->where('order_number', 'like', "%$search%");
    //             $query->orWhereHas('contactUser', function ($query) use ($search) {
    //                 $query->where('users.name', 'like', "%$search%");
    //             });
    //             $query->orWhereHas('salesUser', function ($query) use ($search) {
    //                 $query->where('users.name', 'like', "%$search%");
    //             });
    //             $query->orWhereHas('createUser', function ($query) use ($search) {
    //                 $query->where('users.name', 'like', "%$search%");
    //             });
    //         });
    //     }

    //     // cek switch account
    //     if ($account_id) {
    //         $orderLead->where('company_id', $account_id);
    //     }

    //     if ($contact) {
    //         $orderLead->where('contact', $contact);
    //     }

    //     if ($sales) {
    //         $orderLead->where('sales', $sales);
    //     }

    //     if ($status) {
    //         if (is_array($status)) {
    //             $orderLead->whereIn('status', $status);
    //         } else {
    //             $orderLead->whereIn('status', explode(',', $status));
    //         }
    //     }

    //     if ($created_at) {
    //         if (is_array($created_at)) {
    //             $orderLead->whereBetween('created_at', $created_at);
    //         } else {
    //             $orderLead->whereBetween('created_at', explode(',', $created_at));
    //         }
    //     }

    //     if ($type) {
    //         $orderLead->where('type', $type);
    //     }

    //     if ($print_status) {
    //         $orderLead->where('print_status', $print_status);
    //     }

    //     if ($resi_status) {
    //         $orderLead->where('resi_status', $resi_status);
    //     }

    //     if ($payment_term) {
    //         if (is_array($status)) {
    //             $orderLead->whereIn('payment_term', $payment_term);
    //         } else {
    //             $orderLead->whereIn('payment_term', explode(',', $payment_term));
    //         }
    //     }

    //     if ($sales_channel) {
    //         $orderLead->whereHas('contactUser', function ($query) use ($sales_channel) {
    //             $query->where('sales_channel', $sales_channel);
    //         });
    //     }

    //     if ($role == 'sales') {
    //         $orderLead->where('user_created', $user->id)->orWhere('sales', $user->id);
    //     }

    //     if ($role == 'adminwarehouse') {
    //         $orderLead->where('status', 2);
    //     }

    //     $orderLead->whereNull('parent_id');

    //     $orderLeads = $orderLead->where('status', '>', 0)->orderBy('status', 'asc')->orderBy('created_at', 'desc')->paginate($request->perpage);
    //     // echo"<pre>";print_r($orderLeads);die();
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => tap($orderLeads, function ($order) {
    //             return $order->getCollection()->transform(function ($item) {
    //                 // return $item;
    //                 $orderDeliverys = OrderDelivery::where('uid_lead', $item['uid_lead'])->get();
    //                 $product_needs = ProductNeed::where('uid_lead', $item['uid_lead'])->select(['id', 'uid_lead', 'qty', 'product_id'])->get();
    //                 return [
    //                     'id' => $item['id'],
    //                     'uid_lead' => $item['uid_lead'],
    //                     'order_number' => $item['order_number'],
    //                     'contact_name' => $item['contact_name'],
    //                     'sales_name' => $item['sales_name'],
    //                     'created_by_name' => $item['created_by_name'],
    //                     'created_at' => $item['created_at'],
    //                     'amount' => $item['amount'],
    //                     'total' => $item['total'],
    //                     'payment_term_name' => $item['payment_term_name'],
    //                     'status' => $item['status'],
    //                     'status_submit' => $item['status_submit'],
    //                     'print_status' => $item['print_status'],
    //                     'resi_status' => $item['resi_status'],
    //                     'gp_si_number' => $item['gp_numbers'],
    //                     'product_needs' => $product_needs ?? [],
    //                     'order_delivery' => $orderDeliverys ?? [],
    //                     'billings' => $item['billings'] ?? [],
    //                 ];
    //             });
    //         }),
    //     ]);
    // }

    public function listOrderLead(Request $request)
    {
        $user = auth()->user();
        $role = $user->role->role_type;

        $query = OrderManual::query()
            // ->whereHas('productNeeds')
            ->where('order_manuals.type', 'konsinyasi')
            ->select([
                'order_manuals.id',
                'order_manuals.created_at',
                'order_manuals.status',
                'order_manuals.status_submit',
                'order_manuals.print_status',
                'order_manuals.resi_status',
                'order_manuals.gp_si_number',
                'order_manuals.order_number',
                'order_manuals.invoice_number',
                'order_manuals.uid_lead',
                'order_manuals.contact',
                'order_manuals.sales',
                'order_manuals.payment_term',
                'order_manuals.company_id',
                'order_manuals.user_created',
                'users.name AS contact_name',
                'sales_user.name AS sales_name',
                'created_user.name AS created_by_name',
                'payment_terms.name AS payment_term_name',
                'company_accounts.account_name AS company_name',
            ])
            ->leftJoin('users', function ($join) use ($request) {
                $join->on('users.id', '=', 'order_manuals.contact');
            })
            ->leftJoin('users AS sales_user', function ($join) use ($request) {
                $join->on('sales_user.id', '=', 'order_manuals.sales');
            })
            ->leftJoin('users AS created_user', function ($join) use ($request) {
                $join->on('created_user.id', '=', 'order_manuals.user_created');
            })
            ->leftJoin('payment_terms', function ($join) use ($request) {
                $join->on('order_manuals.payment_term', '=', 'payment_terms.id');
            })
            ->leftJoin('company_accounts', function ($join) use ($request) {
                $join->on('order_manuals.company_id', '=', 'company_accounts.id');
            });

        // Search across multiple fields
        $search = $request->search;
        if ($search) {
            $query->where(function ($subquery) use ($search) {
                $subquery->where('order_number', 'like', "%$search%");
                $subquery->orWhereHas('contactUser', function ($querychild) use ($search) {
                    $querychild->where('users.name', 'like', "%$search%");
                });
                $subquery->orWhereHas('salesUser', function ($querychild) use ($search) {
                    $querychild->where('users.name', 'like', "%$search%");
                });
                $subquery->orWhereHas('createUser', function ($querychild) use ($search) {
                    $querychild->where('users.name', 'like', "%$search%");
                });
            });
        }

        // Other filters (adjust based on your needs)
        if ($request->status) {
            if (is_array($request->status)) {
                $query->whereBetween('order_manuals.status', $request->status);
            } else {
                $query->whereBetween('order_manuals.status', explode(',', $request->status));
            }
        }

        if ($request->account_id) {
            $query->where('order_manuals.company_id', $request->account_id);
        }

        if ($request->created_at) {
            if (is_array($request->created_at)) {
                $query->whereBetween('order_manuals.created_at', $request->created_at);
            } else {
                $query->whereBetween('order_manuals.created_at', explode(',', $request->created_at));
            }
        }

        if ($request->payment_term) {
            if (is_array($request->payment_term)) {
                $query->whereIn('order_manuals.payment_term', $request->payment_term);
            } else {
                $query->whereIn('order_manuals.payment_term', explode(',', $request->payment_term));
            }
        }

        if ($request->contact) {
            $query->where('order_manuals.contact', $request->contact);
        }
        if ($request->sales) {
            $query->where('order_manuals.sales', $request->sales);
        }

        if ($request->print_status) {
            $query->where('order_manuals.print_status', $request->print_status);
        }

        if ($request->resi_status) {
            $query->where('order_manuals.resi_status', $request->resi_status);
        }

        if ($request->order_type) {
            $query->where('order_manuals.order_type', $request->order_type);
        }

        if ($role == 'sales') {
            $query->where(function ($subquery) use ($user) {
                $subquery->where('order_manuals.user_created', $user->id);
                $subquery->orWhere('order_manuals.sales', $user->id);
            });
        }

        // Eager load relationships for efficiency
        $orderLeads = $query->where('order_manuals.status', '>', 0)
            ->orderBy('order_manuals.status', 'asc')
            ->orderBy('order_manuals.created_at', 'desc')
            ->paginate($request->perpage ?? 10);

        // ... transformation logic ...

        return response()->json([
            'status' => 'success',
            'data' => tap($orderLeads, function ($order) {
                return $order->getCollection()->transform(function ($item) {
                    // return $item;
                    $orderDeliverys = $item['status'] == 2 ? OrderDelivery::where('uid_lead', $item['uid_lead'])->select(['id', 'qty_delivered', 'product_need_id', 'invoice_number', 'gp_submit_number', 'submit_klikpajak', 'is_invoice', 'uid_invoice', 'uid_lead'])->get() : [];
                    $product_needs = [];
                    return [
                        'id' => $item['id'],
                        'uid_lead' => $item['uid_lead'],
                        'order_number' => $item['order_number'],
                        'contact_name' => $item['contact_name'],
                        'sales_name' => $item['sales_name'],
                        'created_by_name' => $item['created_by_name'],
                        'created_at' => $item['created_at'],
                        'amount' => $item['amount'],
                        'total' => $item['total'],
                        'payment_term_name' => $item['payment_term_name'],
                        'status' => $item['status'],
                        'status_submit' => $item['status_submit'],
                        'print_status' => $item['print_status'],
                        'resi_status' => $item['resi_status'],
                        'gp_si_number' => $item['gp_si_number'],
                        'product_needs' => $product_needs ?? [],
                        'order_delivery' => $orderDeliverys,
                        'billings' => $item['billings'] ?? [],
                    ];
                });
            }),
        ]);
    }

    public function detailOrderLead($uid_lead)
    {
        $orderLead =  OrderManual::with([
            'billings',
            // 'reminders',
            // 'reminders.userContact',
            'addressUser',
            // 'courierUser',
            'brand',
            'leadActivities',
            'negotiations',
            'warehouse',
            'productNeeds',
            // 'contactUser.addressUsers',
            'orderShipping',
            'orderDelivery'
        ])->where('uid_lead', $uid_lead)->first();

        // echo"<pre>";print_r($orderLead);die();
        return response()->json([
            'status' => 'success',
            'data' => $orderLead,
            'print' => [
                'si' => route('print.si', $uid_lead),
                'so' => route('print.so', $uid_lead),
                'sj' => route('print.sj', $uid_lead),
            ]
        ]);
    }

    public function loadOrderDelivery(Request $request)
    {
        $orderItems = DB::table('order_deliveries')
            ->select(
                'order_deliveries.id',
                'product_needs.product_id',
                'order_deliveries.uid_lead',
                'order_deliveries.product_need_id',
                'order_deliveries.status',
                'order_deliveries.is_invoice',
                DB::raw('SUM(tbl_order_deliveries.qty_delivered) as qty_delivered'),
                'product_variants.name as product_name',
                'product_variants.sku as sku'
            )
            ->leftJoin('product_needs', 'order_deliveries.product_need_id', '=', 'product_needs.id')
            ->leftJoin('product_variants', 'product_needs.product_id', '=', 'product_variants.id')
            ->whereIn('order_deliveries.uid_lead', $request->uid_leads)
            ->groupBy(
                'product_needs.product_id',
            )
            ->get();

        return response()->json([
            'data' => $orderItems,
            'message' => 'success'
        ]);
    }


    // service
    public function getUserContact(Request $request)
    {
        $user = User::where('name', 'like', '%' . $request->search . '%')->whereHas('roles', function ($query) {
            return $query->where('role_type', '!=', 'superadmin');
        })->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }


    function getSalesChannel($account_id = null)
    {
        $data = [
            ['label' => 'Corner', 'value' => 'corner', 'count' => OrderManual::whereType('konsinyasi')->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'corner');
            })->count()],
            ['label' => 'MTP', 'value' => 'mtp', 'count' => OrderManual::whereType('konsinyasi')->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'mtp');
            })->count()],
            ['label' => 'Agent Portal', 'value' => 'agent-portal', 'count' => OrderManual::whereType('konsinyasi')->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'agent-portal');
            })->count()],
            ['label' => 'Distributor', 'value' => 'distributor', 'count' => OrderManual::whereType('konsinyasi')->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'distributor');
            })->count()],
            ['label' => 'Super Agent', 'value' => 'super-agent', 'count' => OrderManual::whereType('konsinyasi')->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'super-agent');
            })->count()],
            ['label' => 'Modern Store', 'value' => 'modern-store', 'count' => OrderManual::whereType('konsinyasi')->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'modern-store');
            })->count()],
            ['label' => 'E-Store', 'value' => 'e-store', 'count' => OrderManual::whereType('konsinyasi')->where('company_id', $account_id)->whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'e-store');
            })->count()],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getUserSales(Request $request)
    {
        $user = User::where('name', 'like', '%' . $request->search . '%')->whereHas('roles', function ($query) {
            return $query->where('role_type', 'sales');
        })->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function changeCourier(Request $request)
    {
        try {
            DB::beginTransaction();
            $orderLead = OrderManual::where('uid_lead', $request->uid_lead)->first();
            $main = AddressUser::where('user_id', $orderLead->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', $orderLead->contact)->first();
            }

            $data = [
                'address_id'  => $main ? $main->id : null,
                'shipping_type'  => 1,
                'courier' => $request->courier
            ];

            $orderLead->update($data);
            $courier = User::find($request->courier);
            createNotification(
                'AGOD200',
                [
                    'user_id' => $orderLead->sales
                ],
                [
                    'user' => $orderLead->salesUser?->name ?? '-',
                    'order_number' => $orderLead->order_number,
                    'title_order' => $orderLead->title,
                    'created_on' => $orderLead->created_at,
                    'contact' => $orderLead->contactUser?->name ?? '-',
                    'assign_by' => auth()->user()->name,
                    'status' => 'Dikirim',
                    'courier_name' => $courier ? $courier->name : '-',
                    'receiver_name' => $main ? $main->nama : '-',
                    'shipping_address' => $main ? $main->alamat_detail : '-',
                    'detail_product' => detailProductOrder($orderLead->productNeeds),
                ],
                ['brand_id' => $orderLead->brand_id]
            );

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Change Courier Order Manual - ' . $request->uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengubah kurir'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah kurir',
                'trace' => $th->getMessage()
            ], 400);
        }
    }

    public function assignWarehouse2($uid_lead, $response = true)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = ['status'  => 2];

            $row = OrderManual::where('uid_lead', $uid_lead)->first();

            if (!$row->courier) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Anda belum memeilih courier',
                ], 400);
            }

            $contact = User::find($row->contact);
            if ($contact) {
                if ($contact->role->rate_limit_status > 0) {
                    if (isset($contact->amount_detail['total_debt'])) {
                        $total_debt = $contact->amount_detail['total_debt'] ?? 0;
                        $rate_limit = (int) $contact->rate_limit;
                        if ($rate_limit > 0) {
                            $total = $row->amount + $total_debt;
                            if ($total > $rate_limit) {
                                DB::rollback();
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Limit tidak cukup, silahkan lakukan pembayaran order..',
                                ], 400);
                            }
                        }
                    }
                }
            }

            // $rowCheck = OrderManual::where('contact', $row->contact)->whereIn('status', [2])->whereType('manual')->orderBy('created_at', 'desc')->first();
            // if ($rowCheck) {
            //     foreach ($rowCheck->productNeeds as $key => $productNeed) {
            //         if (strtotime(date('Y-m-d')) > strtotime($productNeed->due_date)) {
            //             DB::rollback();
            //             return response()->json([
            //                 'status' => 'success',
            //                 'message' => 'Anda masih memiliki order yang jatuh tempo',
            //             ], 400);
            //         }
            //     }
            // }

            if ($row) {
                // check product qty
                // foreach ($row->productNeeds as $key => $item) {
                //     $collection = collect($item->product->stock_warehouse);

                //     // Find the warehouse with ID 4
                //     $warehouse = $collection->where('id', $row->warehouse_id)->first();
                //     if (intval($item->qty) > intval($warehouse['stock'])) {
                //         return response()->json([
                //             'status' => 'error',
                //             'message' => 'Transaksi tidak bisa dilanjutkan karena ada stock yang tidak mencukupi',
                //         ], 400);
                //     }
                // }
                $due_date = Carbon::now()->format('Y-m-d');
                if ($row->paymentTerm) {
                    $due_date = Carbon::now()->addDays($row->paymentTerm->days_of)->format('Y-m-d');
                    $data['due_date'] = $due_date;
                }
                $data['user_updated'] = $user->id;
                $row->update($data);
                $productItems = [];
                foreach ($row->productNeeds as $key => $value) {
                    $productItems[] = [
                        "product_code" => $value->product->sku,
                        "product_name" => $value->product->name,
                        "quantity" => $value->qty * $value->product->qty_bundling,
                        "unit_price" => $value->product->price['final_price'],
                        "weight" => 1
                    ];
                    $variant = ProductVariant::find($value->product_id, ['product_id']);
                    $productMaster = Product::find($variant->product_id);
                    $master_stock = (int) getStock($productMaster->stock_warehouse, $row->master_bin_id);
                    if ($master_stock < $value['qty']) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Stock Produk Tidak Mencukupi',
                            'data' => [
                                'stock' => $master_stock,
                                'qty' => $value->qty
                            ]
                        ], 400);
                    }
                    $this->updateStock($value, $row->master_bin_id, $value['qty']);
                }



                $company = CompanyAccount::find($row->company_id, ['account_code']);
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'submit-so-ethix',
                    'vat' => 0,
                    'tax' => 0,
                    'ref_id' => $uid_lead,
                    'company_id' => $company->account_code
                ]);
                // Send To Ethix
                try {
                    $headers = [
                        'secretcode: ' . getSetting('ETHIX_SECRETCODE_' . $company->account_code),
                        'secretkey: ' . getSetting('ETHIX_SECRETKEY_' . $company->account_code),
                        'Content-Type: application/json'
                    ];

                    $curl_post_data = array(
                        "client_code" => getSetting('ETHIX_CLIENTCODE_' . $company->account_code),
                        "location_code" => $row->warehouse->ethix_id,
                        "courier_name" => "ANTER AJA",
                        "delivery_type" => "REGULER",
                        "order_type" => "DLO",
                        "order_date" => "2023-08-23",
                        "order_code" => $row->order_number,
                        "channel_origin_name" => "FIS",
                        "payment_date" => "2023-08-23 19:29:00",
                        "is_cashless" => true,
                        "recipient_name" => "Fikar",
                        "recipient_phone" => "08888888",
                        "recipient_subdistrict" => "Ciputat",
                        "recipient_district" => "Cipayung",
                        "recipient_city" => "Tangsel",
                        "recipient_province" => "Banten",
                        "recipient_country" => "Indonesia",
                        "recipient_address" => "jalan darat",
                        "recipient_postal_code" => "12270",
                        "Agent" => "Vidi",
                        "product_price" => "20000",
                        "product_discount" => "",
                        "shipping_price" => "",
                        "shipping_discount" => "",
                        "insurance_price" => "",
                        "total_price" => "14750000",
                        "total_weight" => "1",
                        "total_koli" => "1",
                        "cod_price" => "0",
                        "product_discount" => "0",
                        "shipping_price" => "9000",
                        "shipping_discount" => "0",
                        "insurance_price" => "0",
                        "created_via" => "FIS System",
                        "product_information" => $productItems,
                    );

                    setSetting('so_manual_ethix_body', json_encode($curl_post_data));

                    $url = 'https://wms.ethix.id/index.php?r=Externalv2/Order/PostOrder';
                    $handle = curl_init();
                    curl_setopt($handle, CURLOPT_URL, $url);
                    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle, CURLOPT_TIMEOUT, 9000);
                    curl_setopt($handle, CURLOPT_POST, true);
                    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
                    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
                    $responseData = curl_exec($handle);
                    curl_close($handle);

                    setSetting('ethix_so_manual', json_encode($responseData));
                    $responseJSON = json_decode($responseData, true);
                    if (!$responseJSON && is_string($responseData)) {
                        $row->update(['status_ethix_submit' => 'needsubmited']);
                        $orderSi->update(['body' => json_encode($curl_post_data)]);
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $orderSi->id,
                            'order_id' => $row->id
                        ], [
                            'order_submit_log_id' => $orderSi->id,
                            'order_id' => $row->id,
                            'status' => 'failed',
                            'error_message' => $responseData
                        ]);
                        return;
                    }

                    // Check if any error occured
                    if (curl_errno($handle)) {
                        $orderSi->update(['body' => json_encode($curl_post_data)]);
                        $row->update(['status_ethix_submit' => 'needsubmited']);
                        return;
                    }


                    if (isset($responseJSON['status'])) {
                        if (in_array($responseJSON['status'], [200, 201])) {
                            $row->update(['status_ethix_submit' => 'submited']);
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id
                            ], [
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id,
                                'status' => 'success',
                                'error_message' => 'SUCCESS SUBMIT ETHIX'
                            ]);
                        } else {
                            $row->update(['status_ethix_submit' => 'needsubmited']);
                            $orderSi->update(['body' => json_encode($curl_post_data)]);
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id
                            ], [
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['message']
                            ]);
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    setSetting('ethix_so_manual_error', $th->getMessage());
                }

                createNotification(
                    'AGOP200',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser->name,
                        'order_number' => $row->order_number,
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'assign_by' => auth()->user()->name,
                        'status' => 'Diproses Gudang',
                        'courier_name' => '-',
                        'receiver_name' => '-',
                        'shipping_address' => '-',
                        'detail_product' => detailProductOrder($row->productNeeds),
                    ],
                    ['brand_id' => $row->brand_id]
                );
                createNotification(
                    'NORD200',
                    [],
                    [],
                    ['brand_id' => $row->brand_id]
                );

                $dataLog = [
                    'log_type' => '[fis-dev]order_manual',
                    'log_description' => 'Assign Warehouse Order Manual - ' . $uid_lead,
                    'log_user' => auth()->user()->name,
                ];
                CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assign Warehouse Success',
                ]);
            }
            // if ($response) {
            //     return response()->json([
            //         'status' => 'success',
            //         'message' => 'Assign Warehouse Gagal',
            //     ], 400);
            // }
        } catch (\Throwable $th) {
            DB::rollback();
            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assign Warehouse Gagal',
                    'error' => $th->getMessage()
                ], 400);
            }
        }
    }

    public function assignWarehouse($uid_lead, $response = true)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = ['status'  => 2];

            $row = OrderManual::with('productNeeds')->where('uid_lead', $uid_lead)->first();

            if (!$row->courier) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Anda belum memeilih courier',
                ], 400);
            }

            $contact = User::find($row->contact);
            if ($contact) {
                if ($contact->role->rate_limit_status > 0) {
                    if (isset($contact->amount_detail['total_debt'])) {
                        $total_debt = $contact->amount_detail['total_debt'] ?? 0;
                        $rate_limit = (int) $contact->rate_limit;
                        if ($rate_limit > 0) {
                            $total = $row->amount + $total_debt;
                            if ($total > $rate_limit) {
                                DB::rollback();
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Limit tidak cukup, silahkan lakukan pembayaran order..',
                                ], 400);
                            }
                        }
                    }
                }
            }

            // $rowCheck = OrderManual::where('contact', $row->contact)->whereIn('status', [2])->whereType('manual')->orderBy('created_at', 'desc')->first();
            // if ($rowCheck) {
            //     foreach ($rowCheck->productNeeds as $key => $productNeed) {
            //         if (strtotime(date('Y-m-d')) > strtotime($productNeed->due_date)) {
            //             DB::rollback();
            //             return response()->json([
            //                 'status' => 'success',
            //                 'message' => 'Anda masih memiliki order yang jatuh tempo',
            //             ], 400);
            //         }
            //     }
            // }

            if ($row) {
                // check product qty
                // foreach ($row->productNeeds as $key => $item) {
                //     $collection = collect($item->product->stock_warehouse);

                //     // Find the warehouse with ID 4
                //     $warehouse = $collection->where('id', $row->warehouse_id)->first();
                //     if (intval($item->qty) > intval($warehouse['stock'])) {
                //         return response()->json([
                //             'status' => 'error',
                //             'message' => 'Transaksi tidak bisa dilanjutkan karena ada stock yang tidak mencukupi',
                //         ], 400);
                //     }
                // }
                $due_date = Carbon::now()->format('Y-m-d');
                if ($row->paymentTerm) {
                    $due_date = Carbon::now()->addDays($row->paymentTerm->days_of)->format('Y-m-d');
                    $data['due_date'] = $due_date;
                }
                $data['user_updated'] = $user->id;
                $row->update($data);
                $productItems = [];
                $uid_delivery = generateUid();
                $delivery_number = OrderDelivery::generateDeliveryNumberNumber();
                foreach ($row->productNeeds as $key => $value) {
                    $productItems[] = [
                        "product_code" => $value->product->sku,
                        "product_name" => $value->product->name,
                        "quantity" => $value->qty * $value->product->qty_bundling,
                        "unit_price" => $value->product->price['final_price'],
                        "weight" => 1
                    ];

                    if ($row->type == 'konsinyasi') {

                        $termDays = $row->paymentTerm?->days_of ?? 0;
                        $curentDate = Carbon::now()->addDays($termDays + 15);
                        $data = [
                            'uid_lead' => $uid_lead,
                            'uid_delivery' => $uid_delivery,
                            'product_need_id' => $value->id,
                            'user_id' => auth()->user()->id,
                            'qty_delivered' => $value->qty,
                            'delivery_date' => date('Y-m-d'),
                            'status' => 'packing',
                            'type_so' => 'order-konsinyasi',
                            'delivery_number' => $delivery_number,
                            'due_date' => $curentDate
                        ];

                        OrderDelivery::create($data);
                        $value->update(['qty_delivered' => $value->qty]);

                        $variant = ProductVariant::find($value->product_id, ['product_id']);
                        $productMaster = Product::find($variant->product_id);
                        $master_stock = (int) getStock($productMaster->stock_warehouse, $row->master_bin_id);
                        // if ($master_stock < $value['qty']) {
                        //     DB::rollBack();
                        //     return response()->json([
                        //         'status' => 'error',
                        //         'message' => 'Stock Produk Tidak Mencukupi',
                        //         'data' => [
                        //             'stock' => $master_stock,
                        //             'qty' => $value->qty
                        //         ]
                        //     ], 400);
                        // }
                        $productNeed = ProductNeed::find($value['id']);
                        if ($row->order_type == 'new') {
                            $this->updateStock($productNeed, $row->warehouse_id, $value['qty']);
                        }

                        $subQty = $value['qty'];
                        $binStock = getStockBin($row->master_bin_id, $row->company_id, $productNeed->product?->product_id);
                        $stockActual = $binStock - $subQty;
                        MasterBinStock::where('master_bin_id', $row->master_bin_id)->where('product_id', $productNeed->product?->product_id)->where('company_id', $row->company_id)->where('product_variant_id', $productNeed->product_id)->where('stock_type', 'new')->delete();
                        $qty_bundling = $productNeed->product?->qty_bundling > 0 ? $productNeed->product?->qty_bundling : 1;
                        MasterBinStock::updateOrCreate([
                            'master_bin_id' => $row->master_bin_id,
                            'product_id' => $productNeed->product?->product_id,
                            'product_variant_id' => $productNeed->product_id,
                            'company_id' => $row->company_id,
                        ], [
                            'master_bin_id' => $row->master_bin_id,
                            'product_id' => $productNeed->product?->product_id,
                            'product_variant_id' => $productNeed->product_id,
                            'company_id' => $row->company_id,
                            'stock' => floor($stockActual / $qty_bundling) ?? 0,
                            'stock_type' => 'new',
                            'description' => "Order Konsinyasi Barang"
                        ]);
                    }
                }

                $company = CompanyAccount::find($row->company_id, ['account_code']);
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'submit-so-ethix',
                    'vat' => 0,
                    'tax' => 0,
                    'ref_id' => $uid_lead,
                    'company_id' => $company->account_code
                ]);
                // Send To Ethix
                try {
                    $headers = [
                        'secretcode: ' . getSetting('ETHIX_SECRETCODE_' . $company->account_code),
                        'secretkey: ' . getSetting('ETHIX_SECRETKEY_' . $company->account_code),
                        'Content-Type: application/json'
                    ];

                    $curl_post_data = array(
                        "client_code" => getSetting('ETHIX_CLIENTCODE_' . $company->account_code),
                        "location_code" => $row->warehouse->ethix_id,
                        "courier_name" => "ANTER AJA",
                        "delivery_type" => "REGULER",
                        "order_type" => "DLO",
                        "order_date" => "2023-08-23",
                        "order_code" => $row->order_number,
                        "channel_origin_name" => "FIS",
                        "payment_date" => "2023-08-23 19:29:00",
                        "is_cashless" => true,
                        "recipient_name" => "Fikar",
                        "recipient_phone" => "08888888",
                        "recipient_subdistrict" => "Ciputat",
                        "recipient_district" => "Cipayung",
                        "recipient_city" => "Tangsel",
                        "recipient_province" => "Banten",
                        "recipient_country" => "Indonesia",
                        "recipient_address" => "jalan darat",
                        "recipient_postal_code" => "12270",
                        "Agent" => "Vidi",
                        "product_price" => "20000",
                        "product_discount" => "",
                        "shipping_price" => "",
                        "shipping_discount" => "",
                        "insurance_price" => "",
                        "total_price" => "14750000",
                        "total_weight" => "1",
                        "total_koli" => "1",
                        "cod_price" => "0",
                        "product_discount" => "0",
                        "shipping_price" => "9000",
                        "shipping_discount" => "0",
                        "insurance_price" => "0",
                        "created_via" => "FIS System",
                        "product_information" => $productItems,
                    );

                    setSetting('so_manual_ethix_body', json_encode($curl_post_data));

                    $url = 'https://wms.ethix.id/index.php?r=Externalv2/Order/PostOrder';
                    $handle = curl_init();
                    curl_setopt($handle, CURLOPT_URL, $url);
                    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle, CURLOPT_TIMEOUT, 9000);
                    curl_setopt($handle, CURLOPT_POST, true);
                    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
                    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
                    $responseData = curl_exec($handle);
                    curl_close($handle);

                    setSetting('ethix_so_manual', json_encode($responseData));
                    $responseJSON = json_decode($responseData, true);
                    if (!$responseJSON && is_string($responseData)) {
                        $row->update(['status_ethix_submit' => 'needsubmited']);
                        $orderSi->update(['body' => json_encode($curl_post_data)]);
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $orderSi->id,
                            'order_id' => $row->id
                        ], [
                            'order_submit_log_id' => $orderSi->id,
                            'order_id' => $row->id,
                            'status' => 'failed',
                            'error_message' => $responseData
                        ]);
                        return;
                    }

                    // Check if any error occured
                    if (curl_errno($handle)) {
                        $orderSi->update(['body' => json_encode($curl_post_data)]);
                        $row->update(['status_ethix_submit' => 'needsubmited']);
                        return;
                    }


                    if (isset($responseJSON['status'])) {
                        if (in_array($responseJSON['status'], [200, 201])) {
                            $row->update(['status_ethix_submit' => 'submited']);
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id
                            ], [
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id,
                                'status' => 'success',
                                'error_message' => 'SUCCESS SUBMIT ETHIX'
                            ]);
                        } else {
                            $row->update(['status_ethix_submit' => 'needsubmited']);
                            $orderSi->update(['body' => json_encode($curl_post_data)]);
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id
                            ], [
                                'order_submit_log_id' => $orderSi->id,
                                'order_id' => $row->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['message']
                            ]);
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    setSetting('ethix_so_manual_error', $th->getMessage());
                }

                createNotification(
                    'AGOP200',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser->name,
                        'order_number' => $row->order_number,
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser->name,
                        'assign_by' => auth()->user()->name,
                        'status' => 'Diproses Gudang',
                        'courier_name' => '-',
                        'receiver_name' => '-',
                        'shipping_address' => '-',
                        'detail_product' => detailProductOrder($row->productNeeds),
                    ],
                    ['brand_id' => $row->brand_id]
                );
                createNotification(
                    'NORD200',
                    [],
                    [],
                    ['brand_id' => $row->brand_id]
                );

                $dataLog = [
                    'log_type' => '[fis-dev]order_manual',
                    'log_description' => 'Assign Warehouse Order Manual - ' . $uid_lead,
                    'log_user' => auth()->user()->name,
                ];
                CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assign Warehouse Success',
                ]);
            }
            // if ($response) {
            //     return response()->json([
            //         'status' => 'success',
            //         'message' => 'Assign Warehouse Gagal',
            //     ], 400);
            // }
        } catch (\Throwable $th) {
            DB::rollback();
            if ($response) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assign Warehouse Gagal',
                    'error' => $th->getMessage()
                ], 400);
            }
        }
    }

    public function getListBilling($uid_lead)
    {
        $data = LeadBilling::where('uid_lead', $uid_lead)->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function billing(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'uid_lead' => $request->uid_lead,
                'account_name' => $request->account_name,
                'account_bank' => $request->account_bank,
                'total_transfer' => $request->total_transfer,
                'transfer_date' => $request->transfer_date,
                'status' => 0,
            ];
            if ($request->upload_billing_photo) {
                $file = $this->uploadImage($request, 'upload_billing_photo');
                $data['upload_billing_photo'] = $file;
            }

            if ($request->upload_transfer_photo) {
                $file = $this->uploadImage($request, 'upload_transfer_photo');
                $data['upload_transfer_photo'] = $file;
            }


            $billing = LeadBilling::create($data);

            $products = json_decode($request->products, true);
            foreach ($products as $key => $value) {
                $dataBilling = [
                    'uid_lead' => $request->uid_lead,
                    'billing_id' => $billing->id,
                    'product_id' => $value['product_id'],
                    'qty_billing' => $value['qty'],
                ];

                OrderProductBilling::create($dataBilling);
            }



            $row = OrderManual::where('uid_lead', $request->uid_lead)->first();

            if ($row) {
                createNotification(
                    'BILL20',
                    [
                        // 'user_id' => $row->sales
                    ],
                    [
                        'order_number' => $row->order_number,
                        'name' => $row->salesUser?->name ?? '-',
                        'submit_by' => auth()->user()->name,
                        'account_name' => $request->account_name,
                        'account_bank' => $request->account_bank,
                        'total_transfer' => $request->total_transfer,
                        'transfer_date' => $request->transfer_date,
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Create Billing Order Manual - ' . $request->uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

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
                'ERROR' => $th->getMessage()
            ], 400);
        }
    }

    public function setDelivery(Request $request)
    {
        DB::beginTransaction();
        try {
            $row = OrderManual::where('uid_lead', $request->uid_lead)->first();
            if ($row) {
                $data = ['status_pengiriman'  => $request->status];
                if ($request->status == 1) {
                    // $item = ProductNeed::where('uid_lead', $request->uid_lead)->get();
                    //pengurangan stock
                    // foreach ($item as $it) {
                    //     $prod = ProductStock::where('warehouse_id', $row->warehouse_id)->where('product_variant_id', $it->product_id)->first();
                    //     $prod->update(['stock' => $prod->stock - $it->qty]);
                    // }

                    $data = ['status_pengiriman'  => $request->status];
                }
                $row->update($data);

                $dataLog = [
                    'log_type' => '[fis-dev]order_manual',
                    'log_description' => 'Set Delivery Order Manual - ' . $request->uid_lead,
                    'log_user' => auth()->user()->name,
                ];
                CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Set Delivery Success',
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Set Delivery Gagal',
            ], 400);
        } catch (\Throwable $th) {
            // throw $th;
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Set Delivery Gagal',
            ], 400);
        }
    }

    public function billingVerify(Request $request)
    {
        try {
            DB::beginTransaction();

            $billing = LeadBilling::find($request->id);

            if ($billing) {
                $payment_number = $this->generatePaymentNumber($billing->uid_lead);
                $billing->update(['status' => $request->status, 'notes' => $request->notes, 'approved_by' => auth()->user()->id, 'approved_at' => date('Y-m-d H:i:s'), 'payment_number'  => $payment_number]);

                if ($request->status == 1) {
                    foreach ($billing->orderProductBillings as $key => $value) {
                        $product_need = ProductNeed::where('product_id', $value->product_id)->where('uid_lead', $billing->uid_lead)->first();
                        $product_need->update(['qty_dibayar' => $product_need->qty_dibayar + $value->qty_billing]);
                    }
                    if ($billing->total_transfer < $request->amount) {
                        if ($request->deposite > 0) {
                            $amount = $request->deposite + $billing->total_transfer;
                            $final_amount = $amount - $request->amount;
                            $amount_total  = $final_amount - $request->deposite;
                            LeadBilling::create([
                                'uid_lead' => $billing->uid_lead,
                                'account_name' => '-',
                                'account_bank' => '-',
                                'total_transfer' => $amount_total > 0 ? $amount_total : $request->deposite,
                                'transfer_date' => date('Y-m-d'),
                                'status' => 1,
                                'upload_billing_photo' => null,
                                'upload_transfer_photo' => null,
                                'notes' => 'Deposite',
                                'approved_by' => $billing->approved_by,
                                'approved_at' => date('Y-m-d H:i:s'),
                            ]);

                            OrderDeposit::create([
                                'uid_lead' => $billing->uid_lead,
                                'amount' => $amount_total > 0 ? -$amount_total : -$request->deposite,
                                'order_type' => 'manual',
                                'contact' => $billing->orderManual->contact,
                            ]);
                        }
                    } else {
                        if ($request->billing_approved > 0) {
                            $amount_total = $request->billing_approved + $billing->total_transfer - $request->amount;
                            OrderDeposit::create([
                                'uid_lead' => $billing->uid_lead,
                                'amount' => $amount_total,
                                'order_type' => 'manual',
                                'contact' => $billing->orderLead->contact,
                            ]);
                        }
                    }
                }

                if ($billing->total_transfer > $request->amount) {
                    OrderDeposit::create([
                        'uid_lead' => $billing->uid_lead,
                        'amount' => $billing->total_transfer - $request->amount,
                        'order_type' => 'manual',
                        'contact' => $billing->orderManual->contact,
                    ]);
                }

                // send notification
                $row = OrderManual::where('uid_lead', $billing->uid_lead)->first();
                if ($row) {
                    $notification_code = $request->status == 1 ? 'AGOACC200' : 'AGODC200';
                    createNotification(
                        $notification_code,
                        [
                            'user_id' => $row->sales
                        ],
                        [
                            'user' => $row->salesUser?->name,
                            'order_number' => $row->order_number,
                            'title_order' => $row->title,
                            'created_on' => $row->created_at,
                            'contact' => $row->contactUser?->name,
                            'assign_by' => auth()->user()->name,
                            'status' => 'Dikirim',
                            'courier_name' => $row->courierUser ? $row->courierUser->name : '-',
                            'receiver_name' => $row->addressUser ? $row->addressUser->nama : '-',
                            'shipping_address' => $row->addressUser ? $row->addressUser->alamat_detail : '-',
                            'detail_product' => detailProductOrder($row->productNeeds),
                        ],
                        ['brand_id' => $row->brand_id]
                    );
                }

                $dataLog = [
                    'log_type' => '[fis-dev]order_manual',
                    'log_description' => 'Billing Verify Order Manual - ' . $billing->uid_lead,
                    'log_user' => auth()->user()->name,
                ];
                CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Billing Berhasil Diupdate',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Billing Gagal Diupdate',
            ], 400);
        }
    }

    public function cancel($uid_lead)
    {
        try {
            DB::beginTransaction();
            $row = OrderManual::where('uid_lead', $uid_lead)->first();
            $row->update(['status' => 4]);
            createNotification(
                'AGOC200',
                [
                    'user_id' => $row->sales
                ],
                [
                    'user' => $row->salesUser->name,
                    'order_number' => $row->order_number,
                    'title_order' => $row->title,
                    'created_on' => $row->created_at,
                    'contact' => $row->contactUser->name,
                    'assign_by' => auth()->user()->name,
                    'status' => 'Order Dibatalkan',
                    'courier_name' => $row->courierUser ? $row->courierUser->name : '-',
                    'receiver_name' => $row->addressUser ? $row->addressUser->nama : '-',
                    'shipping_address' => $row->addressUser ? $row->addressUser->alamat_detail : '-',
                    'detail_product' => detailProductOrder($row->productNeeds),
                ],
                ['brand_id' => $row->brand_id]
            );

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Cancel Order Manual - ' . $uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'error',
                'message' => 'Order Berhasil Dibatalkan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Order Gagal Dibatalkan',
            ]);
        }
    }

    public function setClosed($uid_lead)
    {
        $data = ['status'  => 3];

        try {
            DB::beginTransaction();
            $row = OrderManual::where('uid_lead', $uid_lead);
            $row->update($data);
            $row->first()->logPrintOrders()->delete();

            DB::commit();

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Set Close Order Manual - ' . $uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            return response()->json([
                'status' => 'success',
                'message' => 'Order Berhasil Ditutup',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'success',
                'message' => 'Order Gagal Ditutup',
                'error' => $th->getMessage()
            ], 400);
        }

        // $row2 = LeadBilling::where('uid_lead', $uid_lead)->where('status', null)->get();
        // foreach ($row2 as $key => $value) {
        //     $value->update(['payment_number'  => $this->generatePaymentNumber($key + 1)]);
        // }


    }

    // reminders
    public function saveReminder(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'uid_lead' => $request->uid_lead,
                'contact' => $request->contact,
                'before_7_day' => false,
                'before_3_day' => false,
                'before_1_day' => false,
                'after_7_day' => false,
            ];
            LeadReminder::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Create Reminder Order Manual - ' . $request->uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Reminder Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder Gagal Disimpan',
            ]);
        }
    }

    public function updateReminder(Request $request)
    {
        SaveReminderActivity::dispatch($request->all())->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Reminder Berhasil Disimpan',
        ]);
    }

    public function deleteReminder($reminder_id)
    {
        try {
            DB::beginTransaction();
            LeadReminder::find($reminder_id)->delete();
            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Delete Reminder Order Manual - ' . $reminder_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Reminder Berhasil Dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder Gagal Dihapus',
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
        $file = Storage::disk('s3')->put('upload/user', $request[$path], 'public');
        return $file;
    }


    // public function saveOrderKonsinyasi(Request $request)
    // {
    //     echo"<pre>";print_r($request->all());die();
    //     try {
    //         DB::beginTransaction();

    //         $user = auth()->user();
    //         $role_type = $user->role->role_type;
    //         $role = 'SA';
    //         $brand = Brand::find($request->brand_id, ['name']);
    //         $brand =  $brand ? strtoupper($brand->name) : 'FLIMTY';
    //         $sales = $request->sales;
    //         $account_id = $request->account_id;
    //         if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
    //             $role = 'AD';
    //         }

    //         if ($role_type == 'sales') {
    //             $sales = $user->id;
    //         }

    //         $uid_lead = $request->uid_lead ?? hash('crc32', Carbon::now()->format('U'));
    //         $kode_unik = $request->kode_unik ?? $this->getUniqueCodeLead();
    //         $data = [
    //             'uid_lead' => $uid_lead,
    //             'type_customer' => $request->type_customer,
    //             'contact'  => $request->contact,
    //             'sales'  => $sales,
    //             'brand_id'  => $request->brand_id,
    //             'warehouse_id' => $request->master_bin_id,
    //             'payment_term'  => $request->payment_terms,
    //             'preference_number' => $request->preference_number,
    //             'customer_need'  => $request->customer_need,
    //             'status'  => $request->status,
    //             'user_created' => $user->id,
    //             'address_id'  => $request->address_id,
    //             'type' => 'konsinyasi',
    //             'master_bin_id' => $request->master_bin_id,
    //             'company_id' => $account_id,
    //             'title' =>  $this->generateTitle($brand, $role),
    //             'order_number' => $request->order_number ?? $this->generateOrderNo(),
    //             'invoice_number' => $request->invoice_number ?? $this->generateInvoiceNo(),
    //             'transfer_number' => $request->transfer_number,
    //             'kode_unik' =>  $kode_unik,
    //             'temp_kode_unik' =>  $kode_unik,
    //             'id_konsinyasi' => $request->so_konsinyasi,
    //             'expired_at' =>  $request->expired_at,
    //             'user_created' => auth()->user()->id,
    //         ];

    //         if ($request->status == 2) {
    //             $courier = User::whereHas('roles', function ($q) {
    //                 return $q->where('role_type', 'warehouse');
    //             })->first(['id']);
    //             $main = AddressUser::where('user_id', $request->contact)->where('is_default', 1)->first(['id']);
    //             if (empty($main)) {
    //                 $main = AddressUser::where('user_id', $request->contact)->first(['id']);
    //             }
    //             $data['address_id'] = $main ? $main->id : null;
    //             $data['shipping_type'] = 1;
    //             $data['courier'] = $courier ? $courier->id : null;
    //         }

    //         $order = OrderManual::updateOrCreate(['uid_lead' => $uid_lead], $data);

    //         // save product needs
    //         if (is_array($request->product_items) && count($request->product_items) > 0) {
    //             $productItemIds = array_column($request->product_items, 'id');

    //             // Fetch existing ProductNeed records for the uid_lead
    //             $existingProductNeeds = ProductNeed::where('uid_lead', $order->uid_lead)->select(['id'])->get();

    //             // Delete product needs that are not in $request->product_items
    //             foreach ($existingProductNeeds as $existingProductNeed) {
    //                 if (!in_array($existingProductNeed->id, $productItemIds)) {
    //                     $existingProductNeed->delete();
    //                 }
    //             }

    //             // Insert or update the product items
    //             foreach ($request->product_items as $key => $item) {
    //                 ProductNeed::updateOrCreate(['id' => $item['id']], [
    //                     'uid_lead' => $order->uid_lead,
    //                     'price' => $item['price_nego'],
    //                     'qty' => $item['qty'],
    //                     'tax_id' => $item['tax_id'],
    //                     'discount' => $item['discount'],
    //                     'product_id' => $item['product_id'],
    //                     'user_created' => $user->id,
    //                 ]);
    //             }
    //         }

    //         // payment type konsinyasi
    //         if ($request->status == 2) {
    //             $this->assignWarehouse($uid_lead, false);
    //         }

    //         // send notification
    //         if ($sales) {
    //             $userSales = User::find($sales, ['name']);
    //             if ($request->status > 0) {
    //                 createNotification(
    //                     'ANL200',
    //                     [
    //                         'user_id' => $sales
    //                     ],
    //                     [
    //                         'sales' => $userSales->name,
    //                         'assign_by' => $user->name,
    //                         'lead_title' => $this->generateTitle($brand, $role),
    //                         'date_assign' => $order->created_at,
    //                         'due_date' => $order->created_at->addDays(1),
    //                         'contact' => $order->contact_name_only,
    //                         'company' => $order->company_name,
    //                         'status_lead' => getStatusOrderLead($order->status),
    //                     ],
    //                     ['brand_id' => $request->brand_id]
    //                 );
    //             }
    //         }

    //         DB::commit();
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Data berhasil disimpan',
    //             'data' => $order
    //         ]);
    //     } catch (\Throwable $th) {
    //         DB::rollback();
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $th->getMessage(),
    //         ]);
    //     }
    // }

    public function saveOrderKonsinyasi(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $role_type = $user->role->role_type;
            $role = 'SA';
            $brand = Brand::find($request->brand_id, ['name']);
            $brand = $brand ? strtoupper($brand->name) : 'FLIMTY';
            $sales = $request->sales;
            $account_id = $request->account_id;
            if (in_array($role_type, ['adminsales', 'leadwh', 'superadmin'])) {
                $role = 'AD';
            }

            if ($role_type == 'sales') {
                $sales = $user->id;
            }

            $uid_lead = $request->uid_lead ?? generateUid();
            $kode_unik = $request->kode_unik ?? $this->getUniqueCodeLead();

            // Store the ID of the first saved order
            // $firstOrderId = null;

            // Initialize the first contact
            // $firstContact = null;

            // if (empty($request->so_konsinyasi)) {
            //     return response()->json(['status' => 'error', 'message' => 'Order transfer not found'], 404);
            // }

            // Iterate over the array of konsinyasi IDs
            // $konsi_ids = is_array($request->so_konsinyasi) ? $request->so_konsinyasi : [$request->so_konsinyasi];
            // foreach ($konsi_ids as $konsinyasi_id) {
            // Fetch the contact for the current konsinyasi_id from order_transfer
            // $orderTransfer = OrderTransfer::where('id', $konsinyasi_id)->first();
            // if (!$orderTransfer) {
            //     return response()->json(['status' => 'error', 'message' => 'Order transfer not found'], 404);
            // }

            // Get the contact from order_transfer
            // $currentContact = $orderTransfer->contact;

            // // Initialize or compare the contact
            // if (!isset($firstContact)) {
            //     // Set the first contact
            //     $firstContact = $currentContact;
            // } elseif ($firstContact !== $currentContact) {
            //     // Return an error response if any contact differs from the first contact
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Maaf, nomor so yang anda pilih, memiliki contact yang berbeda.',
            //     ]);
            // }
            $order_number = $request->order_number ?? OrderManual::generateOrderNumber(6);
            $invoice_number = $request->invoice_number ?? OrderManual::generateInvoiceNumber(6);
            $data = [
                'uid_lead' => $uid_lead,
                'type_customer' => $request->type_customer,
                'contact' => $request->contact,
                'sales' => $sales,
                'brand_id' => $request->brand_id,
                'warehouse_id' => 19,
                'payment_term' => $request->payment_terms,
                'preference_number' => $request->preference_number,
                'customer_need' => $request->customer_need,
                'status' => $request->status,
                'user_created' => $user->id,
                'address_id' => $request->address_id,
                'type' => 'konsinyasi',
                'master_bin_id' => $request->master_bin_id,
                'company_id' => $account_id,
                'title' => $order_number,
                'order_number' => $request->order_number ?? $order_number,
                'invoice_number' => $request->invoice_number ?? $invoice_number,
                'transfer_number' => $request->transfer_number,
                'kode_unik' => $kode_unik,
                'temp_kode_unik' => $kode_unik,
                // 'id_konsinyasi' => $konsinyasi_id,
                'expired_at' => $request->expired_at,
                'notes' => $request->notes,
                'user_created' => $user->id,
                'order_type' => $request->order_type
            ];
            // echo"<pre>";print_r($data);
            // Add the parent_id if this is not the first order
            // if ($firstOrderId) {
            //     $data['parent_id'] = $firstOrderId;
            // }

            $order = OrderManual::updateOrCreate(['uid_lead' => $uid_lead], $data);

            // Set the firstOrderId if it is not already set
            // if (!$firstOrderId) {
            //     $firstOrderId = $order->id;
            // }

            // Save product needs
            if (is_array($request->product_items) && count($request->product_items) > 0) {
                // $productItemIds = array_column($request->product_items, 'id');

                // // Fetch existing ProductNeed records for the uid_lead and konsinyasi_id
                // $existingProductNeeds = ProductNeed::where('uid_lead', $order->uid_lead)
                //     ->select(['id'])
                //     ->get();

                // // Delete product needs that are not in $request->product_items
                // foreach ($existingProductNeeds as $existingProductNeed) {
                //     if (!in_array($existingProductNeed->id, $productItemIds)) {
                //         $existingProductNeed->delete();
                //     }
                // }

                // Insert or update the product items


                foreach ($request->product_items as $key => $item) {
                    ProductNeed::updateOrCreate(['id' => $item['id']], [
                        'uid_lead' => $order->uid_lead,
                        'price' => $item['price_nego'],
                        'qty' => $item['qty'],
                        'tax_id' => $item['tax_id'],
                        'discount' => $item['discount'],
                        'product_id' => $item['product_id'],
                        'product_id' => $item['product_id'],
                        'user_created' => $user->id,
                    ]);
                }
                UpdatePriceQueue::dispatch($order)->onQueue('queue-backend');
            }

            // Payment type konsinyasi
            // if ($request->status == 2) {
            //     $this->assignWarehouse($uid_lead, false);
            // }

            // Send notification
            if ($sales) {
                $userSales = User::find($sales, ['name']);
                if ($request->status > 0) {
                    createNotification(
                        'ANL200',
                        [
                            'user_id' => $sales
                        ],
                        [
                            'sales' => $userSales->name,
                            'assign_by' => $user->name,
                            'lead_title' => $this->generateTitle($brand, $role),
                            'date_assign' => $order->created_at,
                            'due_date' => $order->created_at->addDays(1),
                            'contact' => $order->contact_name_only,
                            'company' => $order->company_name,
                            'status_lead' => getStatusOrderLead($order->status),
                        ],
                        ['brand_id' => $request->brand_id]
                    );
                }
            }
            // }
            // die();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }


    public function updateOrderKonsinyasi(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = [
                'address_id'  => $request->address_user_id,
                'contact'  => $request->contact,
            ];

            $uid_lead = $request->uid_lead;
            $order = OrderManual::where('uid_lead', $uid_lead)->first();
            $order->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Update Order Manual - ' . $uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function selectProductItems(Request $request)
    {
        $data = ['uid_lead' => $request->uid_lead];

        if ($request->item_id) {
            $data['id'] = $request->item_id;
        }

        // $price = $request->price_nego ?? $request->final_price;
        // if ($price < 1) {
        //     if (!$request->discount_id) {
        //         $product = ProductVariant::where('id', $request->product_id)->first();
        //         if ($product) {
        //             $price = $product->price['final_price'];
        //         }
        //     }
        // }

        ProductNeed::updateOrCreate($data, [
            'uid_lead' => $request->uid_lead,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'price' => $request->price_nego,
            'discount' => $request->discount,
            'tax_id' => $request->tax_id,
            'user_updated' => Auth::user()->id,
            'status' => 9,
            'price_type' => 'product'
        ]);

        if ($request->tax_id) {
            ProductNeed::where('uid_lead', $request->uid_lead)->update(['tax_id' => $request->tax_id]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil ditambahkan',
            'product' => null
        ]);
    }

    public function addProductItem(Request $request)
    {
        $checkProductNeed = ProductNeed::whereNotNull('tax_id')->where('uid_lead', $request->uid_lead)->first(['tax_id']);
        $product_need = $request->all();
        $product_need['user_created'] = Auth::user()->id;
        $product_need['user_updated'] = Auth::user()->id;
        if ($checkProductNeed) {
            $product_need['tax_id'] = $checkProductNeed->tax_id;
        }
        // $product_need['price_type'] = 'manual';
        ProductNeed::create($product_need);
        $retur = OrderManual::where('uid_lead', $request->uid_lead)->first();
        if ($request->newData) {
            OrderManual::updateOrCreate(['uid_lead' => $request->uid_lead], [
                'uid_lead' => $request->uid_lead,
                'status' => $retur ? $retur->status : -1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item Product berhasil ditambahkan!'
        ]);
    }

    public function deleteProductItem(Request $request)
    {
        ProductNeed::find($request->item_id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Product berhasil dihapus'
        ]);
    }

    public function addQty(Request $request)
    {
        $item = ProductNeed::find($request->item_id);
        if ($item->qty) {
            $item->increment('qty');
        }
        return response()->json([
            'status' => 'success',
            'data' => $item->product
        ]);
    }

    public function removeQty(Request $request)
    {
        $item = ProductNeed::find($request->item_id);
        if ($item->qty > 1) {
            $item->decrement('qty');
        }
        return response()->json([
            'status' => 'success',
        ]);
    }

    private function generateTitle($brand = 'FLIMTY', $role)
    {
        $date = date('m/Y');
        $brand = strtoupper(str_replace(' ', '-', $brand));
        $title = 'SO/' . $brand . '/' . $role . '-' . $date;
        $data = DB::select("SELECT * FROM `tbl_order_manuals` where title like '%$title%' order by id desc limit 0,1");
        $count_code = 8 + strlen($brand) + strlen($role) + strlen($date);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->title, $count_code);
                $next = sprintf("%03d", ((int)$awal + 1));
                $nomor = 'SO/' . $brand . '/' . $role . '-' . $date . '/' . $next;
            }
        } else {
            $nomor = 'SO/' . $brand . '/' . $role . '-' . $date . '/' . '001';
        }
        return $nomor;
    }


    private function generateOrderNo($no = 1)
    {
        $username = auth()->user()->username ?? '99'; // Pastikan username adalah string
        $year = date('Y');
        $order_number_prefix = 'SO/' . $year . '/2';
        $nomor = $order_number_prefix . $username . '000001';

        $rw = OrderManual::whereNotNull('order_number')
            ->where('type', 'konsinyasi')
            ->orderBy('id', 'desc')
            ->orderBy('order_number', 'desc')
            ->first(['order_number']);

        if ($rw) {
            $awal = substr($rw->order_number, -6);

            if (!is_numeric($awal)) {
                throw new \Exception('Invalid order number format');
            }

            $next = '2' . $username . sprintf("%06d", ((int)$awal + $no));
            $nomor = $order_number_prefix . $next;

            $row = OrderManual::where('order_number', $nomor)->first(['order_number']);
            if ($row) {
                // Increment the next number if there's a conflict
                $next_incremented = '2' . $username . sprintf("%06d", ((int)$awal + $no + 1));
                $nomor = $order_number_prefix . $next_incremented;
            }

            return $nomor;
        }

        return $nomor;
    }


    private function generatePaymentNumber($uid_lead)
    {
        $lastPo = LeadBilling::whereNotNull('payment_number')->orderBy('id', 'desc')->first(['payment_number']);
        $number = '0001';
        if ($lastPo) {
            $number = substr($lastPo->payment_number, -4);
            $number = sprintf("%04d", ((int)$number + 1));
        }
        return 'PAY/' . date('Y') . '/' . $number;
    }

    private function generateInvoiceNo()
    {
        $username = auth()->user()->username ?? 99;
        $year = date('Y');
        $nomor = 'SI/' . $year . '/2' . $username . '000001';
        $rw = OrderManual::whereNotNull('invoice_number')->whereType('konsinyasi')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first(['invoice_number']);

        if ($rw) {
            $awal = substr($rw->invoice_number, -6);
            $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
            $nomor = 'SI/' . $year . '/2' . $next;
        }

        return $nomor;
    }

    public function getUidLead()
    {
        $uid_lead = hash('crc32', Carbon::now()->format('U'));
        OrderManual::create([
            'uid_lead' => $uid_lead,
            'status' => -1,
            'user_created' => auth()->user()->id
        ]);
        return response()->json([
            'status' => 'success',
            'data' => $uid_lead
        ]);
    }

    public function getProductNeed($uid_lead)
    {
        $product_need = ProductNeed::with('product')->where('uid_lead', $uid_lead)->get();

        return response()->json([
            'status' => 'success',
            'data' => $product_need
        ]);
    }

    public function updateStock($trans, $warehouse_id, $qty_delivery = 0)
    {
        try {
            DB::beginTransaction();
            $orderManual = OrderManual::where('uid_lead', $trans->uid_lead)->first(['company_id']);
            $company_id = $orderManual->company_id;
            $product = ProductVariant::find($trans->product_id);
            if ($product->is_bundling > 0) {
                $bundlings = ProductVariantBundling::where('product_variant_id', $trans->product_id)->get();
                foreach ($bundlings as $key => $bundling) {
                    $product_variants = ProductVariant::where('product_id', $bundling->product_id)->get();
                    $master_stock = (int) getStock(getStockWarehouse($product->product_id, $company_id, $warehouse_id), $warehouse_id, $company_id, $product->product_id);
                    foreach ($product_variants as $key => $variant) {
                        $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('company_id', $company_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                        $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                        $qty_stock = $qty_delivery > 0 ? $qty_delivery : $trans->qty;
                        $qty = $bundling_qty * $qty_stock;
                        foreach ($variant_stocks as $key => $stock) {
                            $stok = $stock->qty;
                            $temp = $stok - $qty;
                            $temp = $temp < 0 ? 0 : $temp;
                            $stock_of_market = $stock->stock_of_market - $qty;
                            $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
                            if ($temp >= 0) {
                                $stock->update(['qty' => $temp, 'stock_of_market' => floor($temp / $bundling_qty)]);
                            } else {
                                $stock->update(['qty' => 0, 'stock_of_market' => 0]);
                                $qty = $qty - $stok;
                            }
                        }

                        saveLogStock([
                            'product_id' => $bundling->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $warehouse_id,
                            'type_product' => 'variant',
                            'type_stock' => 'out',
                            'type_transaction' => 'manual',
                            'type_history' => 'so',
                            'name' => 'Transaction product product',
                            'qty' => $qty,
                            'first_stock' => $master_stock,
                            'description' => 'Transaction Product SO Manual - ' . $trans->uid_lead,
                        ]);
                    }

                    $qty_master = $qty_stock * $product->qty_bundling;
                    saveLogStock([
                        'product_id' => $bundling->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $warehouse_id,
                        'type_product' => 'master',
                        'type_stock' => 'out',
                        'type_transaction' => 'manual',
                        'type_history' => 'so',
                        'name' => 'Transaction product product',
                        'qty' => $qty_master,
                        'first_stock' => $master_stock,
                        'description' => 'Transaction Product SO Manual - ' . $trans->uid_lead,
                    ]);

                    $data_stock_1 = [
                        // 'uid_inventory'  => $uid_inventory,
                        'warehouse_id'  => $warehouse_id,
                        'product_id'  => $bundling->product_id,
                        'stock'  => $master_stock - $qty_master,
                        'ref' => "manual - $trans->uid_lead",
                        'company_id' => $company_id,
                        'is_allocated' => 1,
                    ];
                    ProductStock::updateOrCreate([
                        'warehouse_id'  => $warehouse_id,
                        'product_id'  => $bundling->product_id,
                        'company_id' => $company_id,
                    ], $data_stock_1);
                }
            } else {
                $product_variants = ProductVariant::where('product_id', $product->product_id)->get();
                $master_stock = (int) getStock(getStockWarehouse($product->product_id, $company_id, $warehouse_id), $warehouse_id, $company_id, $product->product_id);
                foreach ($product_variants as $key => $variant) {
                    $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('company_id', $company_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                    $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                    $qty_stock = $qty_delivery > 0 ? $qty_delivery : $trans->qty;
                    $qty = $bundling_qty * $qty_stock;
                    foreach ($variant_stocks as $key => $stock) {
                        $stok = $stock->qty;
                        $temp = $stok - $qty;
                        $temp = $temp < 0 ? 0 : $temp;
                        $stock_of_market = $stock->stock_of_market - $qty;
                        $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
                        if ($temp >= 0) {
                            $stock->update(['qty' => $temp, 'stock_of_market' => floor($temp / $bundling_qty)]);
                        } else {
                            $stock->update(['qty' => 0, 'stock_of_market' => 0]);
                            $qty = $qty - $stok;
                        }
                    }

                    saveLogStock([
                        'product_id' => $product->product_id,
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $warehouse_id,
                        'type_product' => 'variant',
                        'type_stock' => 'out',
                        'type_transaction' => 'manual',
                        'type_history' => 'so',
                        'name' => 'Transaction product product',
                        'qty' => $qty,
                        'first_stock' => $master_stock,
                        'description' => 'Transaction Product SO Manual - ' . $trans->uid_lead,
                    ]);
                }

                $qty_master = $qty_stock * $product->qty_bundling;
                saveLogStock([
                    'product_id' => $product->product_id,
                    'product_variant_id' => null,
                    'warehouse_id' => $warehouse_id,
                    'type_product' => 'master',
                    'type_stock' => 'out',
                    'type_transaction' => 'manual',
                    'type_history' => 'so',
                    'name' => 'Transaction product product',
                    'qty' => $qty_master,
                    'first_stock' => $master_stock,
                    'description' => 'Transaction Product SO Manual - ' . $trans->uid_lead,
                ]);

                $data_stock_1 = [
                    // 'uid_inventory'  => $uid_inventory,
                    'warehouse_id'  => $warehouse_id,
                    'product_id'  => $product->product_id,
                    'stock'  => $master_stock - $qty_master,
                    'ref' => "manual - $trans->uid_lead",
                    'company_id' => $company_id,
                    'is_allocated' => 1,
                ];

                ProductStock::updateOrCreate([
                    'warehouse_id'  => $warehouse_id,
                    'product_id'  => $product->product_id,
                    'company_id' => $company_id,
                ], $data_stock_1);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            setSetting("UPDATE_STOCK_ORDER_MANUAL_ERROR_{$trans->id}_{$warehouse_id}", $th->getMessage());
        }
    }


    // save order split delivery
    public function splitOrderDelivery(Request $request)
    {
        try {
            DB::beginTransaction();
            $products = json_decode($request->products, true);
            $uid_delivery = hash('crc32', Carbon::now()->format('U'));
            $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
            $delivery_number = OrderDelivery::generateDeliveryNumberNumber();
            foreach ($products as $key => $value) {
                $termDays = $orderManual->paymentTerm?->days_of ?? 0;
                $curentDate = Carbon::now()->addDays($termDays + 15);
                $data = [
                    'uid_lead' => $request->uid_lead,
                    'uid_delivery' => $uid_delivery,
                    'product_need_id' => $value['id'],
                    'user_id' => auth()->user()->id,
                    'qty_delivered' => $value['qty'],
                    'resi' => $request->resi,
                    'courier' => $request->courier,
                    'sender_name' => $request->sender_name,
                    'sender_phone' => $request->sender_phone,
                    'delivery_date' => date('Y-m-d'),
                    'status' => 'packing',
                    'type_so' => 'order-manual',
                    'delivery_number' => $delivery_number,
                    'due_date' => $curentDate
                ];

                $items = $request->items;
                if ($items && is_array($items)) {
                    $files = [];
                    foreach ($items as $key => $item) {
                        $file = Storage::disk('s3')->put('upload/attachment', $item, 'public');
                        $files[] = $file;
                    }

                    $data['attachments'] = implode(',', $files);
                }

                OrderDelivery::create($data);
                $productNeed = ProductNeed::find($value['id']);
                $productNeed->update([
                    'qty_delivery' => $productNeed->qty_delivery + $value['qty'],
                    // 'invoice_number' => $invoice_number,
                    // 'delivery_number' => $delivery_number,
                    // 'due_date' => $curentDate
                ]);
                // if ($orderManual->master_bin_id) {
                //     MasterBinStock::create([
                //         'master_bin_id' => $orderManual->master_bin_id,
                //         'product_variant_id' => $productNeed->id,
                //         'stock' => $value['qty'],
                //         'description' => "Pengiriman Barang"
                //     ]);
                // }

                $variant = ProductVariant::find($productNeed->product_id, ['product_id']);
                $productMaster = Product::find($variant->product_id);
                $master_stock = (int) getStock($productMaster->stock_warehouse, $orderManual->master_bin_id);
                if ($master_stock < $value['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Stock Produk Tidak Mencukupi',
                        'data' => [
                            'stock' => $master_stock,
                            'qty' => $productNeed->qty
                        ]
                    ], 400);
                }
                $this->updateStock($productNeed, $orderManual->master_bin_id, $value['qty']);
            }
            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Split Order Manual - ' . $request->uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengiriman Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Pengiriman Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    // cancel order delivery
    public function cancelOrderDelivery($delivery_id)
    {
        try {
            DB::beginTransaction();
            $delivery = OrderDelivery::find($delivery_id);
            $delivery->update([
                'status' => 'cancel'
            ]);
            // $productNeed = ProductNeed::find($delivery->product_need_id, ['uid_lead', 'id', 'product_id', 'qty_delivery']);
            // if ($productNeed) {
            //     $row = OrderManual::where('uid_lead', $productNeed->uid_lead)->first(['master_bin_id', 'company_id', 'warehouse_id']);
            //     $productNeed->update([
            //         'qty_delivery' => $productNeed->qty_delivery - $delivery->qty_delivered,
            //     ]);

            //     if ($row->master_bin_id) {
            //         MasterBinStock::create([
            //             'master_bin_id' => $row->master_bin_id,
            //             'product_variant_id' => $productNeed->id,
            //             'stock' => -$delivery->qty_delivered,
            //             'description' => "Pengiriman Barang Dibatalkan"
            //         ]);
            //     }

            //     $variant = ProductVariant::find($productNeed->product_id, ['id', 'product_id', 'qty_bundling', 'is_bundling']);
            //     if ($variant->is_bundling > 0) {
            //         $bundlings = ProductVariantBundling::where('product_variant_id', $productNeed->product_id)->get();
            //         foreach ($bundlings as $key => $bundling) {
            //             $product = Product::find($bundling->product_id);
            //             $master_stock = (int) getStock($product->stock_warehouse, $row->warehouse_id);
            //             $qty_bundling = $bundling->product_qty > 0 ? $bundling->product_qty : 1;
            //             $variants = ProductVariant::where('product_id', $bundling->product_id)->get();
            //             $total_qty_master = $delivery->qty_delivered * $qty_bundling;
            //             $current_stock = $master_stock + $total_qty_master;
            //             foreach ($variants as $variant_item) {
            //                 $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
            //                 $data_stock = [
            //                     'product_variant_id'  => $variant_item->id,
            //                     'warehouse_id'  => $row->warehouse_id,
            //                     'qty'  => $current_stock,
            //                     'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
            //                     'company_id' => $row->company_id,
            //                 ];
            //                 ProductVariantStock::updateOrCreate([
            //                     'product_variant_id'  => $variant_item->id,
            //                     'warehouse_id'  => $row->warehouse_id,
            //                     'company_id' => $row->company_id,
            //                 ], $data_stock);
            //                 saveLogStock([
            //                     'product_id' => $variant_item->product_id,
            //                     'product_variant_id' => $variant_item->id,
            //                     'warehouse_id' => $row->warehouse_id,
            //                     'type_product' => 'variant',
            //                     'type_stock' => 'in',
            //                     'type_transaction' => null,
            //                     'type_history' => 'so',
            //                     'name' => 'return product',
            //                     'qty' => floor($current_stock / $qty_bundling_variant),
            //                     'description' => 'Pengembalian Stock - ' . $row->uid_inventory,
            //                 ]);
            //             }

            //             $total_qty = $delivery->qty_delivered * $qty_bundling;
            //             $data_stock_1 = [
            //                 'uid_inventory'  => null,
            //                 'warehouse_id'  => $row->warehouse_id,
            //                 'product_id'  => $bundling->product_id,
            //                 'stock'  => $master_stock + $total_qty,
            //                 'ref' => '-',
            //                 'company_id' => $row->company_id,
            //                 'is_allocated' => 1,
            //             ];
            //             ProductStock::updateOrCreate([
            //                 'warehouse_id'  => $row->warehouse_id,
            //                 'product_id'  => $bundling->product_id,
            //                 'company_id' => $row->company_id,
            //             ], $data_stock_1);
            //             saveLogStock([
            //                 'product_id' => $bundling->product_id,
            //                 'product_variant_id' => null,
            //                 'warehouse_id' => $row->warehouse_id,
            //                 'type_product' => 'master',
            //                 'type_stock' => 'in',
            //                 'type_transaction' => null,
            //                 'type_history' => 'so',
            //                 'name' => 'return product',
            //                 'qty' => $total_qty,
            //                 'description' => 'Pengembalian Stock - ' . $row->uid_inventory,
            //             ]);
            //         }
            //     } else {
            //         $product = Product::find($variant->product_id);
            //         $master_stock = (int) getStock($product->stock_warehouse, $row->warehouse_id);
            //         $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
            //         $variants = ProductVariant::where('product_id', $variant->product_id)->get();
            //         $total_qty_master = $delivery->qty_delivered * $qty_bundling;
            //         $current_stock = $master_stock + $total_qty_master;
            //         foreach ($variants as $variant_item) {
            //             // $master_stock = (int) getStock($variant->stock_warehouse, $row->warehouse_id);
            //             $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
            //             $data_stock = [
            //                 'product_variant_id'  => $variant_item->id,
            //                 'warehouse_id'  => $row->warehouse_id,
            //                 'qty'  => $current_stock,
            //                 'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
            //                 'company_id' => $row->company_id,
            //             ];
            //             ProductVariantStock::updateOrCreate([
            //                 'product_variant_id'  => $variant_item->id,
            //                 'warehouse_id'  => $row->warehouse_id,
            //                 'company_id' => $row->company_id,
            //             ], $data_stock);
            //             saveLogStock([
            //                 'product_id' => $variant_item->product_id,
            //                 'product_variant_id' => $variant_item->id,
            //                 'warehouse_id' => $row->warehouse_id,
            //                 'type_product' => 'variant',
            //                 'type_stock' => 'in',
            //                 'type_transaction' => null,
            //                 'type_history' => 'so',
            //                 'name' => 'return product',
            //                 'qty' => floor($current_stock / $qty_bundling_variant),
            //                 'description' => 'Pengembalian Stock - ' . $row->uid_inventory,
            //             ]);
            //         }

            //         $total_qty = $delivery->qty_delivered * $qty_bundling;
            //         $data_stock_1 = [
            //             'uid_inventory'  => null,
            //             'warehouse_id'  => $row->warehouse_id,
            //             'product_id'  => $variant->product_id,
            //             'stock'  => $master_stock + $total_qty,
            //             'ref' => '-',
            //             'company_id' => $row->company_id,
            //             'is_allocated' => 1,
            //         ];
            //         ProductStock::updateOrCreate([
            //             'warehouse_id'  => $row->warehouse_id,
            //             'product_id'  => $variant->product_id,
            //             'company_id' => $row->company_id,
            //         ], $data_stock_1);
            //         saveLogStock([
            //             'product_id' => $variant->product_id,
            //             'product_variant_id' => null,
            //             'warehouse_id' => $row->warehouse_id,
            //             'type_product' => 'master',
            //             'type_stock' => 'in',
            //             'type_transaction' => null,
            //             'type_history' => 'so',
            //             'name' => 'return product',
            //             'qty' => $total_qty,
            //             'description' => 'Pengembalian Stock - ' . $row->uid_inventory,
            //         ]);
            //     }
            // }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengiriman Berhasil Dibatalkan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Pengiriman Gagal Dibatalkan',
                'ERROR' => $th->getMessage()
            ], 400);
        }
    }

    public function saveOrderShipping(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'uid_lead' => $request->uid_lead,
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                'resi' => $request->resi,
                'expedition_name' => $request->expedition_name,
                'order_type' => 'manual',
                'created_by' => auth()->user()->id,
                'delivery_date' => $request->delivery_date,
            ];

            $items = $request->items;
            if (is_array($items) && count($items) > 0) {
                $files = [];
                foreach ($items as $key => $item) {
                    $file = Storage::disk('s3')->put('upload/attachment', $item, 'public');
                    $files[] = $file;
                }

                $data['attachment'] = implode(',', $files);
            }

            OrderShipping::updateOrCreate(['uid_lead' => $request->uid_lead], $data);
            $row = OrderManual::where('uid_lead', $request->uid_lead)->first();
            if ($request->resi) {
                $row->update(['resi_status' => 'done']);
            }
            if ($row) {
                createNotification(
                    'SOR200',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'name' => $row->salesUser?->name ?? '-',
                        'submit_by' => auth()->user()->name,
                        'sender_name' => $request->sender_name,
                        'sender_phone' => $request->sender_phone,
                        'resi' => $request->resi,
                        'expedition_name' => $request->expedition_name,
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }

            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Save Shipping Order Manual - ' . $request->uid_lead,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengiriman Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Pengiriman Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function deleteUniqueCode(Request $request)
    {
        try {
            DB::beginTransaction();
            $row = OrderManual::where('uid_lead', $request->uid_lead)->first();
            if ($row) {
                $row->update(['kode_unik' => $request->kode_unik]);
            }
            DB::commit();
            if ($row) {
                UpdatePriceQueue::dispatch($row)->onQueue('queue-backend');
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Kode Unik Berhasil Di',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Unik Gagal Di',
            ]);
        }
    }

    // update ongkos kirim
    public function updateOngkosKirim(Request $request, $uid_lead)
    {
        try {
            DB::beginTransaction();
            $row = OrderManual::where('uid_lead', $uid_lead)->first();
            if ($row) {
                $row->update(['ongkir' => $request->ongkir]);
            }
            DB::commit();
            if ($row) {
                UpdatePriceQueue::dispatch($row)->onQueue('queue-backend');
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Ongkos Kirim Berhasil Diupdate',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ongkos Kirim Gagal Diupdate',
            ]);
        }
    }

    protected function generateHmacHeader($method, $path, $body = '')
    {
        $username = 'JgMP9NMoBLtdr5Wh';
        $secret = 'g41RMUBzJ670LJ2sQazBOEnbuprRAsyy';

        // Set timezone ke UTC
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $dateString = $date->format(\DateTime::RFC2822);

        $bodyDigest = '';
        if (!empty($body)) {
            $bodyHash = hash('sha256', $body, true);
            $bodyDigest = 'SHA-256=' . base64_encode($bodyHash);
        }

        $requestLine = $method . ' ' . $path . ' HTTP/1.1';
        $payload = implode("\n", ['date: ' . $dateString, $requestLine]);
        $digest = hash_hmac('sha256', $payload, $secret, true);
        $signature = base64_encode($digest);

        $header = 'hmac username="' . $username . '", algorithm="hmac-sha256", headers="date request-line", signature="' . $signature . '"';

        $idempotencyKey = Uuid::uuid4()->toString(); //diganti menggunakan hasil dari hashcode body

        return [
            'Date' => $dateString,
            'Authorization' => $header,
            'Digest' => $bodyDigest,
            'X-Idempotency-Key' => $idempotencyKey,
            'Content-Type' => 'application/json',
        ];
    }

    // update invoiced
    public function updateInvoiced(Request $request, $product_need_id = null)
    {
        $url = 'https://sandbox-api.mekari.com/v2/klikpajak/v1/efaktur/out?auto_approval=false&auto_calculate=true';

        try {
            DB::beginTransaction();

            if ($request->items && is_array($request->items)) {
                $this->submitBulkKlikpajak($request, false);
            } else {
                $this->submitSingleKlikpajak($request, $product_need_id);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Gagal Disimpan',
                'error' => $th->getMessage()
            ]);
        }
    }

    // get unique code 3 digit max 500 with auto increment
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

    public function export(Request $request)
    {
        //print_r($request);
        $file_name = 'convert/FIS-Order_Manual-' . date('d-m-Y') . '.xlsx';

        Excel::store(new OrderManualExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function exportDetail($uid)
    {
        $file_name = 'convert/FIS-Order_Manual-' . date('d-m-Y') . '.xlsx';

        Excel::store(new OrderManualDetailExport($uid), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('attachment');

            // $exec = Excel::import(new OrderManualImport(), $file);

            $import = new OrderKonsinyasiImport();
            $import->setContact($request->contact);
            Excel::import($import, $file);

            // $rowsWithUserNotFound = @$import->getRowsWithUserNotFound();

            // $rowsWithDataNull = @$import->getRowsWithDataNull();

            // if (!empty($rowsWithUserNotFound)) {
            //     return response()->json([
            //         'status' => 'failed',
            //         'message' => 'UID User tidak terdaftar',
            //         'rows_with_user_not_found' => @$rowsWithUserNotFound,
            //     ], 200);
            // }

            // if (!empty($rowsWithDataNull)) {
            //     return response()->json([
            //         'status' => 'failed',
            //         'message' => 'Data input tidak lengkap',
            //         'rows_with_data_null' => @$rowsWithDataNull,
            //     ], 200);
            // }

            return response()->json(['message' => 'Data berhasil diimpor'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()], 500);
        }
    }

    public function importTransfer(Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('attachment');

            $import = new TransferKonsinyasiImport(auth()->user()->id);
            Excel::import($import, $file);

            return response()->json(['message' => 'Data berhasil diimpor'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()], 500);
        }
    }
}
