<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Exports\OrderLeadExport;
use App\Exports\OrderLeadDetailExport;
use App\Http\Controllers\Spa\Order\GpController;
use App\Jobs\SaveReminderActivity;
use App\Models\CompanyAccount;
use App\Models\InventoryItem;
use App\Models\LeadBilling;
use App\Models\LeadReminder;
use App\Models\MasterBinStock;
use App\Models\OrderDelivery;
use App\Models\OrderDeposit;
use App\Models\OrderLead;
use App\Models\OrderProductBilling;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantBundlingStock;
use App\Models\ProductVariantStock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class OrderLeadController extends GpController
{
    public function index($uid_lead = null)
    {
        return view('spa.spa-index');
    }

    public function listOrderLead(Request $request)
    {
        $search = $request->search;
        $contact = $request->contact;
        $sales = $request->sales;
        $created_at = $request->created_at;
        $status = $request->status;
        $user = auth()->user();
        $role = $user->role->role_type;
        $account_id = $request->account_id;
        $type = $request->type;
        $payment_term = $request->payment_term;
        $print_status = $request->print_status;
        $resi_status = $request->resi_status;
        $sales_channel = $request->sales_channel;

        $orderLead =  OrderLead::query();

        if ($search) {
            $orderLead->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%$search%");
                $query->orWhereHas('contactUser', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
                $query->orWhereHas('salesUser', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
                $query->orWhereHas('createUser', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
            });
        }

        // cek switch account
        if ($account_id) {
            $orderLead->where('company_id', $account_id);
        }

        if ($contact) {
            $orderLead->where('contact', $contact);
        }

        if ($sales) {
            $orderLead->where('sales', $sales);
        }

        if ($status) {
            if (is_array($status)) {
                $orderLead->whereIn('status', $status);
            } else {
                $orderLead->whereIn('status', explode(',', $status));
            }
        }

        if ($created_at) {
            if (is_array($created_at)) {
                $orderLead->whereBetween('created_at', $created_at);
            } else {
                $orderLead->whereBetween('created_at', explode(',', $created_at));
            }
        }

        if ($print_status) {
            $orderLead->where('print_status', $print_status);
        }

        if ($resi_status) {
            $orderLead->where('resi_status', $resi_status);
        }

        if ($payment_term) {
            if (is_array($status)) {
                $orderLead->whereIn('payment_term', $payment_term);
            } else {
                $orderLead->whereIn('payment_term', explode(',', $payment_term));
            }
        }

        if ($sales_channel) {
            $orderLead->whereHas('contactUser', function ($query) use ($sales_channel) {
                $query->where('sales_channel', $sales_channel);
            });
        }

        if ($role == 'sales') {
            $orderLead->where('user_created', $user->id)->orWhere('sales', $user->id);
        }

        if ($role == 'adminwarehouse') {
            $orderLead->where('status', 2);
        }



        $orderLeads = $orderLead->where('status', '>', 0)->orderBy('status', 'asc')->orderBy('created_at', 'desc')->paginate($request->perpage);
        // echo"<pre>";print_r($orderLeads);die();
        return response()->json([
            'status' => 'success',
            'data' => tap($orderLeads, function ($order) {
                return $order->getCollection()->transform(function ($item) {
                    // return $item;
                    $orderDeliverys = OrderDelivery::where('uid_lead', $item['uid_lead'])->get();
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
                        'gp_si_number' => $item['gp_numbers'],
                        'product_needs' => $orderDeliverys ?? [],
                        'order_delivery' => $orderDeliverys ?? [],
                        'billings' => $item['billings'] ?? [],
                    ];
                });
            }),
        ]);
    }


    function getSalesChannel()
    {
        $data = [
            ['label' => 'Corner', 'value' => 'corner', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'corner');
            })->count()],
            ['label' => 'MTP', 'value' => 'mtp', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'mtp');
            })->count()],
            ['label' => 'Agent Portal', 'value' => 'agent-portal', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'agent-portal');
            })->count()],
            ['label' => 'Distributor', 'value' => 'distributor', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'distributor');
            })->count()],
            ['label' => 'Super Agent', 'value' => 'super-agent', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'super-agent');
            })->count()],
            ['label' => 'Modern Store', 'value' => 'modern-store', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'modern-store');
            })->count()],
            ['label' => 'E-Store', 'value' => 'e-store', 'count' => OrderLead::whereHas('contactUser', function ($query) {
                $query->where('sales_channel', 'e-store');
            })->count()],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function detailOrderLead($uid_lead)
    {
        $orderLead =  OrderLead::with([
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
        $orderLead = OrderLead::where('uid_lead', $request->uid_lead)->first();
        $orderLead->courier = $request->courier;
        $orderLead->save();

        $dataLog = [
            'log_type' => '[fis-dev]order_lead',
            'log_description' => 'Change Courier Order Lead - ' . $request->uid_lead,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $orderLead
        ]);
    }

    public function assignWarehouse($uid_lead)
    {
        DB::beginTransaction();
        try {
            $data = ['status'  => 2];

            $row = OrderLead::where('uid_lead', $uid_lead)->first();

            if (!$row->courier) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Anda belum memeilih courier',
                ], 400);
            }

            $contact = $row->contactUser;
            if ($contact) {
                if ($contact->role->rate_limit_status > 0) {
                    if (isset($contact?->amount_detail['total_debt'])) {
                        $total_debt = $contact->amount_detail['total_debt'] ?? 0;
                        $rate_limit = (int) $contact->rate_limit;

                        if ($rate_limit > 0) {
                            $total = $row->amount + $total_debt;
                            if ($total > $rate_limit) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => 'Limit tidak cukup, silahkan lakukan pembayaran order..',
                                ], 400);
                            }
                        }
                    }
                }
            }

            // $row = OrderLead::whereIn('status', [1, 2])->orderBy('created_at', 'desc')->first();
            // if ($row) {
            //     if (strtotime($row->payment_due_date) > strtotime(date('Y-m-d'))) {
            //         DB::rollback();
            //         return response()->json([
            //             'status' => 'success',
            //             'message' => 'Anda masih memiliki order yang jatuh tempo',
            //         ], 400);
            //     }
            // }

            foreach ($row->productNeeds as $key => $item) {
                if (intval($item->qty) > intval($item->product->stock_off_market)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Transaksi tidak bisa dilanjutkan karena ada stock yang tidak mencukupi',
                    ], 400);
                }
            }

            if ($row) {
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
                    // $this->updateStock($value, $row->warehouse_id);
                    if ($value->qty_delivery > 0) {
                        if ($row->master_bin_id) {
                            MasterBinStock::create([
                                'product_variant_id' => $value->product_id,
                                'master_bin_id' => $row->master_bin_id,
                                'stock' => $value->qty_delivery,
                                'description' => "Transaction Dari Order " . $row->order_number
                            ]);
                        }
                    }
                }

                try {
                    $company = CompanyAccount::find($row->company_id, ['account_code']);
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
                        "payment_date" => "2023-08-23 19 =>29 =>00",
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

                    setSetting('so_lead_ethix_body', json_encode($curl_post_data));

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
                    $response = curl_exec($handle);
                    curl_close($handle);
                } catch (\Throwable $th) {
                    //throw $th;
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

                $dataLog = [
                    'log_type' => '[fis-dev]order_lead',
                    'log_description' => 'Assign Warehouse Order Lead - ' . $uid_lead,
                    'log_user' => auth()->user()->name,
                ];
                CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Assign Warehouse Success',
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Assign Warehouse Gagal',
            ], 400);
        } catch (\Throwable $th) {
            throw $th;
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Assign Warehouse Gagal',
            ], 400);
        }
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

            // send notification
            $row = OrderLead::where('uid_lead', $request->uid_lead)->first();
            if ($row) {
                createNotification(
                    'BILL21',
                    [
                        'user_id' => $row->sales
                    ],
                    [
                        'user' => $row->salesUser?->name ?? '-',
                        'order_number' => $billing?->orderLead?->order_number ?? '-',
                        'title_order' => $row->title,
                        'created_on' => $row->created_at,
                        'contact' => $row->contactUser?->name ?? '-',
                        'assign_by' => auth()->user()->name,
                        'status' => 'Qualified',
                        'courier_name' => '-',
                        'receiver_name' => '-',
                        'shipping_address' => '-',
                    ],
                    ['brand_id' => $row->brand_id]
                );
            }
            $dataLog = [
                'log_type' => '[fis-dev]order_lead',
                'log_description' => 'Create Billing Order Lead - ' . $request->uid_lead,
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
            ]);
        }
    }

    public function billingVerify(Request $request)
    {
        try {
            DB::beginTransaction();

            $billing = LeadBilling::find($request->id);

            if ($billing) {
                $payment_number = $this->generatePaymentNumber($billing->uid_lead);
                $billing->update(['status' => $request->status, 'notes' => $request->notes, 'approved_by' => auth()->user()->id, 'approved_at' => date('Y-m-d H:i:s'), 'payment_number' => $payment_number]);

                if ($request->status == 1) {
                    foreach ($billing->orderProductBillings as $key => $value) {
                        if ($billing->orderLead->master_bin_id) {
                            MasterBinStock::create([
                                'master_bin_id' => $billing->orderLead->master_bin_id,
                                'product_variant_id' => $value->product_id,
                                'stock' => -$value->qty_billing,
                                'description' => "Verifikasi Pembayaran $payment_number"
                            ]);
                        }

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
                                'order_type' => 'lead',
                                'contact' => $billing->orderLead->contact,
                            ]);
                        } else {
                            if ($request->billing_approved > 0) {
                                $amount_total = $request->billing_approved + $billing->total_transfer - $request->amount;
                                OrderDeposit::create([
                                    'uid_lead' => $billing->uid_lead,
                                    'amount' => $amount_total,
                                    'order_type' => 'lead',
                                    'contact' => $billing->orderLead->contact,
                                ]);
                            }
                        }
                    }



                    if ($billing->total_transfer > $request->amount) {
                        OrderDeposit::create([
                            'uid_lead' => $billing->uid_lead,
                            'amount' => $billing->total_transfer - $request->amount,
                            'order_type' => 'lead',
                            'contact' => $billing->orderLead->contact,
                        ]);
                    }
                }

                // send notification
                $row = OrderLead::where('uid_lead', $billing->uid_lead)->first();
                if ($row) {
                    $notification_code = $request->status == 1 ? 'AGOACC200' : 'AGODC200';
                    createNotification(
                        $notification_code,
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
                            'status' => 'Dikirim',
                            'courier_name' => $row->courierUser ? $row->courierUser->name : '-',
                            'receiver_name' => $row->addressUser ? $row->addressUser->nama : '-',
                            'shipping_address' => $row->addressUser ? $row->addressUser->alamat_detail : '-',
                            'detail_product' => detailProductOrder($row->productNeeds),
                        ],
                        ['brand_id' => $row->brand_id]
                    );
                }

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
            $row = OrderLead::where('uid_lead', $uid_lead)->first();
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
                'log_type' => '[fis-dev]order_lead',
                'log_description' => 'Cancel Order Lead - ' . $uid_lead,
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

        $row = OrderLead::where('uid_lead', $uid_lead);
        $row->update($data);
        $row->first()->logPrintOrders()->delete();

        $dataLog = [
            'log_type' => '[fis-dev]order_lead',
            'log_description' => 'Set Close Order Lead - ' . $uid_lead,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Order Berhasil Ditutup',
        ]);
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
                'log_type' => '[fis-dev]order_lead',
                'log_description' => 'Create Reminder Order Lead - ' . $request->uid_lead,
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
            'message' => 'Reminder Berhasil Diupdate',
        ]);
    }

    public function deleteReminder($reminder_id)
    {
        try {
            DB::beginTransaction();
            LeadReminder::find($reminder_id)->delete();
            $dataLog = [
                'log_type' => '[fis-dev]order_lead',
                'log_description' => 'Delete Reminder Order Lead - ' . $reminder_id,
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

    // public function updateStock($trans, $warehouse_id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $product = ProductVariant::find($trans->product_id);

    //         $product_variants = ProductVariant::where('product_id', $product->product_id)->get();

    //         $product_master = Product::find($product->product_id);
    //         $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
    //         foreach ($product_variants as $key => $variant) {
    //             $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
    //             $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
    //             $qty = $bundling_qty * $trans->qty;
    //             foreach ($variant_stocks as $key => $stock) {
    //                 $stok = $stock->qty;
    //                 $temp = $stok - $qty;
    //                 $temp = $temp < 0 ? 0 : $temp;
    //                 $stock_of_market = $stock->stock_of_market - $qty;
    //                 $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
    //                 if ($temp >= 0) {
    //                     $stock->update(['qty' => $temp, 'stock_of_market' => floor($temp / $bundling_qty)]);
    //                 } else {
    //                     $stock->update(['qty' => 0, 'stock_of_market' => 0]);
    //                     $qty = $qty - $stok;
    //                 }
    //             }


    //             saveLogStock([
    //                 'product_id' => $product->product_id,
    //                 'product_variant_id' => $variant->id,
    //                 'warehouse_id' => $warehouse_id,
    //                 'type_product' => 'variant',
    //                 'type_stock' => 'out',
    //                 'type_transaction' => 'manual',
    //                 'type_history' => 'so',
    //                 'name' => 'Transaction product product',
    //                 'qty' => $qty,
    //                 'description' => 'Transaction Product SO Lead- ' . $trans->uid_lead,
    //             ]);
    //         }

    //         $qty_master = $trans->qty * $product->qty_bundling;
    //         saveLogStock([
    //             'product_id' => $product->product_id,
    //             'product_variant_id' => null,
    //             'warehouse_id' => $warehouse_id,
    //             'type_product' => 'master',
    //             'type_stock' => 'out',
    //             'type_transaction' => 'manual',
    //             'type_history' => 'so',
    //             'name' => 'Transaction product product',
    //             'qty' => $qty_master,
    //             'description' => 'Transaction Product SO Lead- ' . $trans->uid_lead,
    //         ]);

    //         // foreach ($stockInventories as $key => $stock_master) {
    //         //     $stok_master = $stock_master->stock;
    //         //     $temp_master = $stok_master - $qty_master;
    //         //     if ($temp_master >= 0) {
    //         //         $stock_master->update(['stock' => $temp_master]);
    //         //     } else {
    //         //         $stock_master->update(['stock' => 0]);
    //         //         $qty_master = $qty_master - $stok_master;
    //         //     }
    //         // }

    //         $data_stock_1 = [
    //             // 'uid_inventory'  => $uid_inventory,
    //             'warehouse_id'  => $warehouse_id,
    //             'product_id'  => $product->product_id,
    //             'stock'  => $master_stock - $qty_master,
    //             'ref' => "manual - $trans->uid_lead",
    //             'company_id' => 1,
    //             'is_allocated' => 1,
    //         ];
    //         ProductStock::updateOrCreate([
    //             'warehouse_id'  => $warehouse_id,
    //             'product_id'  => $product->product_id,
    //         ], $data_stock_1);

    //         // foreach ($stockInventories as $stock_master) {
    //         //     if ($qty_master > 0 && $stock_master->stock > 0) {
    //         //         if ($stock_master->stock >= $qty_master) {
    //         //             $stock_master->stock -= $qty_master;
    //         //             $qty_master = 0;
    //         //         } else {
    //         //             $qty_master -= $stock_master->stock;
    //         //             $stock_master->stock = 0;
    //         //         }
    //         //         $stock_master->save();
    //         //     } else {
    //         //         break; // No more quantity to process or stock available
    //         //     }
    //         // }

    //         DB::commit();
    //     } catch (\Throwable $th) {
    //         DB::rollback();
    //         setSetting("UPDATE_STOCK_ORDER_MANUAL_ERROR_{$trans->id}_{$warehouse_id}", $th->getMessage());
    //     }
    // }
    public function updateStock($trans, $warehouse_id, $qty_delivery = 0)
    {
        try {
            DB::beginTransaction();
            $orderManual = OrderLead::where('uid_lead', $trans->uid_lead)->first(['company_id']);
            $company_id = $orderManual->company_id;
            $product = ProductVariant::find($trans->product_id);
            if ($product->is_bundling > 0) {
                $bundlings = ProductVariantBundling::where('product_variant_id', $trans->product_id)->get();
                foreach ($bundlings as $key => $bundling) {
                    $product_variants = ProductVariant::where('product_id', $bundling->product_id)->get();
                    $product_master = Product::find($bundling->product_id);
                    $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
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
                            'description' => 'Transaction Product SO Lead- ' . $trans->uid_lead,
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
                        'description' => 'Transaction Product SO Lead- ' . $trans->uid_lead,
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
                $product_master = Product::find($product->product_id);
                $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
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
                        'description' => 'Transaction Product SO Lead- ' . $trans->uid_lead,
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
                    'description' => 'Transaction Product SO Lead- ' . $trans->uid_lead,
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
            $orderLead = OrderLead::where('uid_lead', $request->uid_lead)->first();
            $invoice_number = $this->generateInvoiceNo();
            $delivery_number = ProductNeed::generateDeliveryNumberNumber();
            foreach ($products as $key => $value) {
                $termDays = $orderLead->paymentTerm?->days_of ?? 0;
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
                    'type_so' => 'order-lead',
                    'invoice_number' => $invoice_number,
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

                $this->updateStock($productNeed, $orderLead->warehouse_id, $value['qty']);

                if ($orderLead->status == 2) {
                    if ($orderLead->master_bin_id) {
                        MasterBinStock::create([
                            'product_variant_id' => $productNeed->product_variant_id,
                            'master_bin_id' => $orderLead->master_bin_id,
                            'stock' => $value['qty'],
                            'description' => "Transaction Dari Order " . $orderLead->order_number
                        ]);
                    }
                }
            }
            $dataLog = [
                'log_type' => '[fis-dev]order_lead',
                'log_description' => 'Split Order Lead - ' . $request->uid_lead,
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

            $productNeed = ProductNeed::find($delivery->product_need_id);
            if ($productNeed) {
                $productNeed->update([
                    'qty_delivery' => $productNeed->qty_delivery - $delivery->qty_delivered,
                ]);

                $variant = ProductVariant::find($productNeed->product_id, ['id', 'product_id']);
                $order = OrderLead::where('uid_lead', $productNeed->uid_lead)->first(['company_id', 'warehouse_id']);
                // balikin stock jika cancel
                $bundlings = ProductVariantBundling::where('product_id', $variant->product_id)->get();
                foreach ($bundlings as $key => $bundling) {
                    $qty_bundling = $bundling->product_qty > 0 ? $bundling->product_qty : 1;
                    $productVariantBundlingStock = ProductVariantBundlingStock::where('warehouse_id', $order->warehouse_id)->where('company_id', $order->company_id)->where('product_variant_id', $variant->id)->first(['qty']);
                    if ($productVariantBundlingStock) {
                        $stockVariantBundling = $productVariantBundlingStock->qty + $delivery->qty_delivered;
                        $productVariantBundlingStock->update([
                            'qty' => $stockVariantBundling,
                            'stock_of_market' => floor($stockVariantBundling / $qty_bundling) ?? 0,
                        ]);
                    } else {

                        ProductVariantBundlingStock::create([
                            'product_variant_bundling_id' => $bundling->id,
                            'qty' => $delivery->qty_delivered,
                            'stock_off_market' => floor($delivery->qty_delivered / $qty_bundling) ?? 0,
                            'warehouse_id' => $order->warehouse_id,
                            'company_id' => $order->company_id,
                            'description' => "Kembalikan stok transaksi"
                        ]);
                    }
                }

                // update stock variant
                $variants = ProductVariant::where('product_id', $variant->product_id)->get();
                foreach ($variants as $variant) {
                    $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                    $productVariantStock = ProductVariantStock::where('warehouse_id', $order->warehouse_id)->where('company_id', $order->company_id)->where('product_variant_id', $variant->id)->first(['qty']);
                    if ($productVariantStock) {
                        $stockVariant = $productVariantStock->qty + $delivery->qty_delivered;
                        $productVariantStock->update([
                            'qty' => $stockVariant,
                            'stock_of_market' => floor($stockVariant / $qty_bundling) ?? 0,
                        ]);
                    } else {
                        ProductVariantStock::create([
                            'product_variant_id' => $variant->id,
                            'qty' => $delivery->qty_delivered,
                            'stock_of_market' => floor($delivery->qty_delivered / $qty_bundling) ?? 0,
                            'warehouse_id' => $order->warehouse_id,
                            'company_id' => $order->company_id,
                        ]);
                    }
                }

                // update stock
                $currentStock = ProductStock::where([
                    'uid_inventory' => '-',
                    'product_id' => $variant->product_id,
                    'warehouse_id' => $order->warehouse_id,
                    'company_id' => $order->company_id,
                ])->first();
                if ($currentStock) {
                    $stock = $currentStock->stock - $delivery->qty_delivered;
                    if ($stock > 0) {
                        $currentStock->update([
                            'stock' => $stock,
                            'is_allocated' => 1
                        ]);
                    } else {
                        $currentStock->update([
                            'warehouse_id' => $order->warehouse_id,
                            'is_allocated' => 1
                        ]);
                    }
                } else {
                    ProductStock::create([
                        'uid_inventory' => '-',
                        'product_id' => $variant->product_id,
                        'warehouse_id' => $order->warehouse_id,
                        'stock' => $delivery->qty_delivered,
                        'is_allocated' => 1,
                        'company_id' => $order->company_id,
                    ]);
                }
            }


            $dataLog = [
                'log_type' => '[fis-dev]order_manual',
                'log_description' => 'Cancel Order Manual Delivery - ' . $delivery_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');


            Db::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengiriman Berhasil Dibatalkan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Pengiriman Gagal Dibatalkan',
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
                'order_type' => 'lead',
                'created_by' => auth()->user()->id,
                'delivery_date' => $request->delivery_date,
            ];

            $items = $request->items;
            $files = [];
            foreach ($items as $key => $item) {
                $file = Storage::disk('s3')->put('upload/attachment', $item, 'public');
                $files[] = $file;
            }

            $data['attachment'] = implode(',', $files);

            OrderShipping::updateOrCreate(['uid_lead' => $request->uid_lead], $data);
            $row = OrderLead::where('uid_lead', $request->uid_lead)->first();
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
                'log_type' => '[fis-dev]order_lead',
                'log_description' => 'Save Shipping Order Lead - ' . $request->uid_lead,
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
            $row = OrderLead::where('uid_lead', $request->uid_lead)->first();
            if ($row) {
                $row->update(['kode_unik' => $request->kode_unik]);
            }
            DB::commit();
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
            $row = OrderLead::where('uid_lead', $uid_lead)->first();
            if ($row) {
                $row->update(['ongkir' => $request->ongkir]);
            }
            DB::commit();
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

    private function generatePaymentNumber($uid_lead)
    {
        $lastPo = LeadBilling::whereNotNull('payment_number')->orderBy('id', 'desc')->first();
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
        $rw = OrderLead::whereNotNull('invoice_number')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first();

        if ($rw) {
            $awal = substr($rw->invoice_number, -6);
            $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
            $nomor = 'SI/' . $year . '/2' . $next;
        }

        return $nomor;
    }

    public function export(Request $request)
    {
        $file_name = 'convert/FIS-Order_Lead-' . date('d-m-Y') . '.xlsx';

        Excel::store(new OrderLeadExport($request), $file_name, 's3', null, [
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
        $orderLead = OrderLead::query();

        $file_name = 'convert/FIS-Order_Lead-' . date('d-m-Y') . '.xlsx';

        Excel::store(new OrderLeadDetailExport($uid), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
}
