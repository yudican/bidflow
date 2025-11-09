<?php

namespace App\Http\Controllers\Spa;

use App\Events\UpdateTransactionEvent;
use App\Jobs\CreateLogQueue;
use App\Exports\TransactionExport;
use App\Exports\TransactionTelmarkExport;
use App\Http\Controllers\Controller;
use App\Jobs\CreateOrderPopaket;
use App\Jobs\GetOrderResi;
use App\Jobs\RequestNewAwbNumber;
use App\Models\Address;
use App\Models\AddressUser;
use App\Models\ConfirmPayment;
use App\Models\Kecamatan;
use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\MasterDiscount;
use App\Models\MasterPph;
use App\Models\Role;
use App\Models\ShippingType;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\TransactionDeliveryStatus;
use App\Models\TransactionStatus;
use App\Models\TransactionDetail;
use App\Models\TransactionLabel;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    public function index($transaction_id = null)
    {
        return view('spa.spa-index');
    }

    public function listTransaction(Request $request)
    {
        $search = $request->search;
        $created_at = $request->created_at;
        $status = $request->status;
        $label_status = $request->status_label;
        $ekspedisi = $request->ekspedisi;
        $status_delivery = $request->status_delivery;
        $payment_method = $request->payment_method_id;
        $type = $request->type;
        $action = $request->action;
        $user_create = $request->user_create;
        $user = $request->user_id ? User::find($request->user_id) : auth()->user();
        $role = $user->role;
        $stage = $request->stage;
        $user_id = $request->user_id;
        $transaction = $this->getTransaction($type);

        if ($action == 'withdrawal') {
            if ($role->role_type == 'ahligizi') {
                $transaction->whereHas('userCreated', function ($query) {
                    return $query->whereHas('roles', function ($query) {
                        return $query->where('role_type', 'ahligizi');
                    });
                });
            }
            $transaction->where('status_delivery', 4)->where('status', 7);
        } else {
            // Handle stage as array
            if (is_array($stage)) {
                $transaction->where(function ($query) use ($stage) {
                    foreach ($stage as $stageValue) {
                        switch ($stageValue) {
                            case 'waiting-payment':
                                $query->orWhere(function ($q) {
                                    $q->where('status', 1);
                                });
                                break;
                            case 'waiting-confirmation':
                                $query->orWhere(function ($q) {
                                    $q->where('status', 2);
                                });
                                break;
                            case 'confirm-payment':
                                $query->orWhere(function ($q) {
                                    $q->where('status', 3);
                                });
                                break;
                            case 'on-process':
                                $query->orWhere(function ($q) {
                                    $q->where('status', 7)
                                        ->where('status_delivery', 1);
                                });
                                break;
                            case 'ready-to-ship':
                                $query->orWhere(function ($q) {
                                    $q->whereIn('status', [3, 7])
                                        ->where('status_delivery', 21);
                                });
                                break;
                            case 'on-delivery':
                                $query->orWhere(function ($q) {
                                    $q->where('status_delivery', 3);
                                });
                                break;
                            case 'delivered':
                                $query->orWhere(function ($q) {
                                    $q->where('status_delivery', 4)
                                        ->where('status', 7);
                                });
                                break;
                            case 'returned':
                                $query->orWhere(function ($q) {
                                    $q->where('status_delivery', 5)
                                        ->where('status', 7);
                                });
                                break;
                            case 'cancelled':
                                $query->orWhere(function ($q) {
                                    $q->whereIn('status', [4, 6]);
                                });
                                break;
                        }
                    }
                });
            } else {
                if ($stage == 'new-order') {
                    if ($role->role_type == 'ahligizi') {
                        $transaction->whereHas('userCreated', function ($query) {
                            return $query->whereHas('roles', function ($query) {
                                return $query->where('role_type', 'ahligizi');
                            });
                        });
                    }
                    $transaction->where('status', 0)->where('status_delivery', 0);
                } else {
                    // stage
                    // stage 1 - Waiting for payment
                    if (in_array($stage, ['waiting-payment'])) {
                        $transaction->where('status', 1);
                    }

                    // stage 2 - Waiting for confirmation
                    if (in_array($stage, ['waiting-confirmation'])) {
                        $transaction->where('status', 2);
                    }

                    // stage 3 - Payment confirmed
                    if (in_array($stage, ['confirm-payment'])) {
                        $transaction->where('status', 3);
                    }

                    // stage 4 - On Process
                    if (in_array($stage, ['on-process'])) {
                        $transaction->where('status', 7)->where('status_delivery', 1);
                    }

                    // stage 5 - Ready to ship
                    if (in_array($stage, ['ready-to-ship'])) {
                        $transaction->whereIn('status', [3, 7])->where('status_delivery', 21);
                    }

                    // stage 6 - On Delivery
                    if (in_array($stage, ['on-delivery'])) {
                        $transaction->where('status_delivery', 3);
                    }

                    // stage 7 - Delivered
                    if (in_array($stage, ['delivered'])) {
                        $transaction->where('status_delivery', 4)->where('status', 7);
                    }

                    // stage 7 - Returned
                    if (in_array($stage, ['returned'])) {
                        $transaction->where('status_delivery', 5)->where('status', 7);
                    }

                    if (in_array($stage, ['report-transaction'])) {
                        // $transaction->where('status_delivery', 4)->where('status', 7)->whereNull('completed_at');
                    }

                    // stage 8 - Cancelled
                    if (in_array($stage, ['cancelled'])) {
                        $transaction->whereIn('status', [4, 6]);
                    }
                }
            }
        }

        if ($search) {
            $transaction->where(function ($subquery) use ($search) {
                $subquery->where('id_transaksi', 'like', "%$search%");
                $subquery->orWhereHas('user', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
                $subquery->orWhereHas('userCreated', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
            });
        }

        if ($label_status) {
            $final_status = $label_status == 10 ? 0 : $label_status;
            $transaction->where('status_label', $final_status);
        }

        if ($ekspedisi) {
            $transaction->whereHas('shippingType', function ($query) use ($ekspedisi) {
                $query->where('shipping_type_name', 'like', '%' . $ekspedisi . '%');
            });
        }


        // end stage
        if (in_array($role->role_type, ['agent', 'subagent'])) {
            $transaction->where('user_id', $user->id);
        }

        // end stage
        if (in_array($role->role_type, ['agent-telmark', 'agent-telmar', 'telmark-supervisor'])) {
            $transaction->where('user_create', $user->id);
        }

        if ($payment_method) {
            $transaction->where('payment_method_id', $payment_method);
        }

        if ($status) {
            $transaction->whereIn('status', $status);
        }

        if ($user_id) {
            $transaction->where('user_id', $user_id);
        }

        if ($status_delivery) {
            $transaction->whereIn('status_delivery', $status_delivery);
        }

        if ($user_create) {
            if (in_array($role->role_type, ['agent-telmar'])) {
                $transaction->where('user_create', $user->id);
            } else {
                $transaction->where('user_create', $user_create);
            }
        }

        if ($created_at) {
            // Assuming $created_at is an array with two elements: start date and end date
            $startDate = $created_at[0];
            $endDate = $created_at[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $transaction->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $transaction->orderBy('created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function getTransactionDetail($transaction_id)
    {
        $transaction = Transaction::with([
            'transactionDetail',
            'shippingType',
            'paymentMethod',
            'addressUser',
        ])->find($transaction_id);

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    public function getTransactionUser($user_id)
    {
        $transactions = Transaction::with([
            'transactionDetail',
            'shippingType',
            'paymentMethod',
            'addressUser',
        ])->where('user_id', $user_id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function getTransactionDetailAgent($transaction_id)
    {
        $transaction = TransactionAgent::with([
            'confirmPayment',
            'transactionDetail',
            'addressUser',
            'logs',
            'shippingType',
            'shipperWarehouse'
        ])->find($transaction_id);

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    public function printInvoice(Request $request)
    {
        $selected =  $request->transaction_id;
        $urls = [];
        $segment = $request->type == 'agent' ? '.agent' : '';
        foreach ($selected as $value) {
            $urls[] = route('invoice.print' . $segment, $value);
        }

        print_invoice($urls);
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mencetak invoice',
            'data' => $urls
        ]);
    }

    // cancel order
    public function postCancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cancel_note' => 'required|string',
            'transactions_id' => 'required||exists:transactions,id',
            // 'transactions_id.*' => 'integer|exists:transactions,id'
        ], [
            'cancel_note.required' => "Masukan catatan  pembatalan",
            'cancel_note.string' => "Catatan harus berupa teks",
            'transactions_id.required' => "Pilih transaksi yang akan dibatalkan",
            // 'transactions_id.array' => "Format tidak sesuai",
            'transactions_id.integer' => "Data yang dimasukan  tidak valid",
            'transactions_id.exists' => "Transaksi tidak ditemukan"
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $trx = Transaction::find($request->transactions_id);
            if (!$trx) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data transaksi tidak ditemukan'
                ], 404);
            }
            if ($trx->status > 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dapat dibatalkan'
                ], 404);
            }

            $data = ['status' => 4, 'status_link' => 0, 'status_delivery' => 1, 'expire_payment' => null, 'paid_time' => null, 'cancel_time' => Carbon::now(), 'cancel_by' => auth()->user()->id, 'note' => $request->cancel_note];
            $trx->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan telah dibatalkan'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membatalkan pesanan',
                'dev_message' => $e->getMessage()
            ], 500);
        }
    }

    // verification payment with type approve and reject
    public function verifyPayment(Request $request, $type)
    {
        $transaction = $this->getTransaction($request->type)->where('id', $request->transaction_id)->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data transaksi tidak ditemukan'
            ], 404);
        }

        $payment = ConfirmPayment::find($request->payment_id);

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pembayaran tidak ditemukan'
            ], 404);
        }

        if ($type == 'approve') {
            $transaction->update([
                'status' => 3,
                'paid_time' => Carbon::now()
            ]);

            $payment->update(['status' => 1]);
        }

        // reject
        if ($type == 'reject') {
            $transaction->update([
                'status' => 4,
                'cancel_time' => Carbon::now(),
                'note' => $request->cancel_note ?? 'Pembayaran Ditolak'
            ]);

            $payment->update(['status' => 2]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data pembayaran telah diupdate'
        ], 200);
    }

    // public function printLabel(Request $request)
    // {
    //     $transactions = $this->getTransaction($request->type)->whereIn('id', $request->transaction_id)->get();

    //     $labels = [];
    //     foreach ($transactions as $key => $transaction) {
    //         if ($transaction->status_delivery == 21) {
    //             $transaction->update(['status_delivery' => 3]);
    //         }
    //         if ($transaction->label) {
    //             LogApproveFinance::create([
    //                 'user_id' => auth()->user()->id,
    //                 'transaction_id' => $transaction->id,
    //                 'keterangan' => 'Cetak Label'
    //             ]);
    //             $transaction->label->update(['status' => 1]);
    //             $paymentData = [
    //                 'transaction_id' => $transaction->id,
    //                 'email' => $transaction->user->email,
    //             ];

    //             event(new UpdateTransactionEvent($paymentData));
    //             $labels[] = $transaction->label->label_url;
    //         }
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Berhasil mencetak label',
    //         'data' => $labels
    //     ]);
    // }

    public function printLabel(Request $request)
    {
        if (count($request->transaction_id) > 0) {
            $awb_numbers = [];
            $labels = [];
            foreach ($request->transaction_id as $transaction_id) {
                $transaction = Transaction::findOrFail($transaction_id);
                try {
                    DB::beginTransaction();
                    TransactionLabel::where('id_transaksi', $transaction->id_transaksi)->update(['status' => 1]);
                    $transaction->update(['status_label' => 1]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
                $awb_numbers[] = $transaction->resi;

                if ($transaction->status_delivery == 21) {
                    $transaction->update(['status_delivery' => 3]);
                }
            }


            $client = new Client();
            $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . '/shipment/v1/orders/label/bulk', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('POPAKET_TOKEN')
                ],
                'json' => [
                    'awb_numbers' => $awb_numbers
                ]
            ]);
            $responseJSON = json_decode($response->getBody(), true);

            if ($responseJSON['status'] == 'success') {
                $labels[] = $responseJSON['data']['url'];

                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil mencetak label',
                    'data' => $labels
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mencetak bulk label'
                ], 400);
            }
        } else {
            $transactions = $this->getTransaction($request->type)->whereIn('id', $request->transaction_id)->get();

            $labels = [];
            foreach ($transactions as $key => $transaction) {
                if ($transaction->status_delivery == 21) {
                    $transaction->update(['status_delivery' => 3]);
                }
                if ($transaction->label) {
                    LogApproveFinance::create([
                        'user_id' => auth()->user()->id,
                        'transaction_id' => $transaction->id,
                        'keterangan' => 'Cetak Label'
                    ]);
                    $transaction->label->update(['status' => 1]);
                    $paymentData = [
                        'transaction_id' => $transaction->id,
                        'email' => $transaction->user->email,
                    ];

                    event(new UpdateTransactionEvent($paymentData));
                    $labels[] = $transaction->label->label_url;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mencetak label',
                'data' => $labels
            ]);
        }
    }

    public function readyToShip(Request $request)
    {
        if (count($request->transaction_id) > 0) {
            DB::beginTransaction();
            $transactions = $this->getTransaction($request->type)->whereIn('id', $request->transaction_id)->get();
            $data = [];
            foreach ($transactions as $key => $transaction) {
                // if ($transaction->awb_status == 2) {
                //     GetOrderResi::dispatch($transaction->id_transaksi)->onQueue('queue-backend');
                // } else {
                //     RequestNewAwbNumber::dispatch($transaction->id_transaksi)->onQueue('queue-backend');
                // }

                $data[] = [
                    'client_order_no' => $transaction->id_transaksi,
                    'pickup_time' => strtotime(Carbon::now()->addDays(1)),
                ];
                $transaction->update(['status_delivery' => 21]);
                TransactionDeliveryStatus::create([
                    'id_transaksi' => $transaction->id_transaksi,
                    'delivery_status' => 21,
                ]);

                if ($transaction->resi) {
                    createNotification('OIR200', [
                        'device_id' => $transaction->user?->device_id,
                        'user_id' => $transaction->user_id,

                    ], ['resi' => $transaction->resi]);
                }

                $paymentData = [
                    'transaction_id' => $transaction->id,
                    'email' => $transaction->user->email,
                ];

                event(new UpdateTransactionEvent($paymentData));
            }

            $dataLog = [
                'log_type' => '[fis-dev]transaction',
                'log_description' => 'Ready To Ship Transaction - ' . $transaction->id_transaksi,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengubah status menjadi siap dikirim',
            ]);
        }
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengubah status menjadi siap dikirim',
        ], 400);
    }

    public function asignToWarehouse(Request $request)
    {
        if (count($request->transaction_id) > 0) {
            $transactions = $this->getTransaction($request->type)->whereIn('id', $request->transaction_id)->get();

            foreach ($transactions as $key => $transaction) {
                if ($transaction) {
                    DB::beginTransaction();
                    try {
                        $transaction->update(['status' => 7, 'status_delivery' => 1]);
                        TransactionStatus::create([
                            'id_transaksi' => $transaction->id_transaksi,
                            'status' => 7,
                        ]);
                        TransactionDeliveryStatus::create([
                            'id_transaksi' => $transaction->id_transaksi,
                            'delivery_status' => 1,
                        ]);

                        // $paymentData = [
                        //     'transaction_id' => $transaction->id,
                        //     'email' => $transaction->user->email,
                        // ];

                        // event(new UpdateTransactionEvent($paymentData));

                        //log approval
                        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction->id, 'keterangan' => 'Assign Warehouse']);
                        // create notification
                        // $data_notification_admin = [
                        //     'user' => auth()->user()->name,
                        //     'rincian_bayar' => getRincianPembayaran($transaction),
                        //     'rincian_transaksi' => getRincianTransaksi($transaction),
                        // ];
                        // createNotification('WPO200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
                        // $notification_data = [
                        //     'user' => $transaction->user->name,
                        //     'invoice' => $transaction->id_transaksi,
                        // ];
                        // createNotification('ODP200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
                        // $dataLog = [
                        //     'log_type' => '[fis-dev]transaction',
                        //     'log_description' => 'Assign To Warehouse Transaction - ' . $transaction->id_transaksi,
                        //     'log_user' => auth()->user()->name,
                        // ];
                        // CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                        DB::commit();
                    } catch (ClientException $th) {
                        DB::rollBack();
                        $response = $th->getResponse();
                        LogError::updateOrCreate(['id' => 1], [
                            'message' => $th->getMessage(),
                            'trace' => $response->getBody()->getContents(),
                            'action' => 'Assign To warehouse (' . $transaction->id_transaksi . ')',
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Assign to warehouse berhasil',
        ]);
    }

    public function trackOrder(Request $request)
    {
        $client = new Client();
        $token = getSetting('POPAKET_TOKEN');
        try {
            $response = $client->request('GET', getSetting('POPAKET_BASE_URL') . "/shipment/v1/orders/{$request->resi}/track", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ],
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                return response()->json([
                    'status' => 'success',
                    'data' => $responseJSON['data']
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data tidak ditemukan',
                'data' => []
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
            ], 400);
        }
    }

    static function getTransaction($type)
    {

        return Transaction::query()->whereType($type);
    }

    public function getPph21($amount = 0)
    {
        $pphs = MasterPph::all();
        $percent = 5;
        foreach ($pphs as $pph) {
            if ($amount <= $pph->pph_amount) {
                $percent = $pph->pph_percentage ? $pph->pph_percentage : 1;
            } else if ($amount >= $pph->pph_amount && $amount <= $pph->pph_amount) {
                $percent = $pph->pph_percentage ? $pph->pph_percentage : 1;
            }
        }

        return $percent / 100;
    }


    public function createNewOrder(Request $request)
    {
        $trans_id = 'INV-1-' . rand(1323, 9999) . date('-dmY-') . date('Hi');

        try {
            DB::beginTransaction();

            // check if user exist
            $user = User::where('email', $request->email)->first();

            $user_id = $request->user_id;
            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'telepon' => formatPhone($request->phone),
                    'password' => Hash::make('admin123'),
                    'created_by' => auth()->user()->id,
                    'sales_channel' => 'e-store',
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

            $shiping_type = ShippingType::create($request->shipping);
            $nominal = 0;

            foreach ($request->products as $key => $product) {
                $discountId = isset($product['discount_id']) ?  $product['discount_id'] : null;
                $discount = $discountId ? MasterDiscount::find($discountId) : null;
                $price = $product['price']['final_price'] * $product['qty'];
                if ($discount) {
                    $percentage = $discount->percentage > 0 ? $discount->percentage / 100 : 0;
                    $amount_diskon = $price * $percentage;
                    $diskon = isset($product['diskon']) ? $product['diskon'] : 0;
                    $nominal += $price - $amount_diskon;
                } else {
                    $nominal += $price;
                }
            }

            // $request['user_id'] = $user_id;
            // $request['nominal'] = $nominal;
            // $voucher = $this->applyVoucher($request);

            // $diskon = isset($voucher['amount_discount']) ? $voucher['amount_discount'] : 0;
            $diskon = $request->diskon;
            $diskon_voucher = $request->diskon_voucher ?? 0;
            $transaction_data = [
                'user_id' => $user_id,
                'id_transaksi' => $trans_id,
                'brand_id' => 8,
                'product_id' => 18,
                'voucher_id' => $request->voucher_id,
                'note' => $request->note,
                'ongkir' => $shiping_type?->shipping_price ?? 0,
                'diskon' => $diskon,
                'diskon_voucher' => $request->diskon_voucher,
                'weight' => $request->weight,
                'diskon_ongkir' => $shiping_type?->shipping_discount ?? 0,
                'amount_to_pay' =>  $nominal + $shiping_type?->shipping_price - $diskon_voucher,
                'nominal' =>  $nominal + $shiping_type?->shipping_price - $diskon_voucher,
                'type' => 'telmart',
                'company_id' => $request->company_id,
                'payment_method_id' => $request->payment_method_id,
                'shipping_type_id' => $shiping_type->id,
                'status' => 0,
                'status_delivery' => 0,
                'user_create' => auth()->user()->id,
                'expire_payment' => Carbon::now()->addDays(1),
            ];

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

                    $address = AddressUser::updateorCreate([
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



            // $nominal = $request->total_harga;
            // $fee = $nominal * 0.25;
            // $dpp = $fee / 1.11;
            // $total_pembagian = $dpp * 0.7;
            // $pph21 = $total_pembagian * $this->getPph21($total_pembagian);
            // $nutrisionist_amount = $total_pembagian - $pph21;

            if (!$address) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat membuat order, alamat tidak lengkap',
                ], 400);
            }

            $address_id =  $address ? $address->id : null;
            $transaction_data['address_user_id'] = $address_id;

            if ($address) {
                $transaction_data['final_address'] = json_encode([
                    'address_user_id' => $address_id,
                    'address' => $address?->alamat_detail,
                    'telepon' => formatPhone($address?->telepon, '+628'),
                    'name' => $address?->nama,
                    'type' => $address?->type,
                ]);
                $transaction_data['data_user_address'] = json_encode([
                    'id' => $address->id,
                    'type' => $address->type,
                    'nama' => $address->nama,
                    'alamat_detail' => $address->alamat_detail,
                    'kodepos' => $address->kodepos,
                    'telepon' => formatPhone($address->telepon, '+628'),
                    'catatan' => $address->catatan,
                ]);
            }
            // $transaction_data['commission'] = $nutrisionist_amount;
            $transaction = Transaction::create($transaction_data);

            foreach ($request->products as $key => $product) {
                $discountId = isset($product['discount_id']) ?  $product['discount_id'] : null;
                $discount = $discountId ? MasterDiscount::find($discountId) : null;
                $diskon = isset($product['diskon']) ? $product['diskon'] : 0;
                $price = $product['price']['final_price'] * $product['qty'];
                $percentage = $discount?->percentage > 0 ? $discount->percentage / 100 : 0;
                $amount_diskon = $price * $percentage;
                $data_detail = [
                    'transaction_id' => $transaction->id,
                    'invoice_id' => $trans_id,
                    'product_id' => $product['product_id'],
                    'product_variant_id' => $product['id'],
                    'qty' => $product['qty'],
                    'discount_id' => $discountId,
                    'diskon' => $amount_diskon,
                    'data_discount' => $discount ? json_encode([
                        'id' => $discount->id,
                        'title' => $discount->title,
                        'percentage' => $discount->percentage,
                        'sales_channel' => $discount->sales_channel,
                        'sales_tag' => $discount->sales_tag,
                    ]) : null,
                    'price' => $product['price']['final_price'],
                    'subtotal' => $price - $amount_diskon,
                    'status' => 1,
                ];


                $transaction->transactionDetail()->create($data_detail);
            }

            // // send notification
            // if ($transaction) {
            //     createNotification(
            //         'TRANS20',
            //         [
            //             'user_id' => $transaction->user_id
            //         ],
            //         [
            //             'user' => $transaction->salesUser?->name ?? '-',
            //             'invoice' => $transaction->id_transaksi,
            //         ],
            //         ['brand_id' => $transaction->brand_id]
            //     );
            // }
            $dataLog = [
                'log_type' => '[fis-dev]transaction',
                'log_description' => 'Create New Order Transaction - ' . $trans_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

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

    // apply voucher
    public function applyVoucher(Request $request)
    {
        $user = null;
        if ($request->user_id) {
            $user = User::find($request->user_id);
        }
        $voucher = Voucher::where('voucher_code', $request->voucher_code)->first();
        // if voucher not found
        if (!$voucher) {
            return response()->json([
                'message' => 'Voucher tidak ditemukan',
                'voucher_id' => null,
                'amount_discount' => 0,
            ], 400);
        }

        if ($voucher->status < 1) {
            return response()->json([
                'message' => 'Voucher sudah tidak aktif',
                'voucher_id' => null,
                'amount_discount' => 0,
            ], 400);
        }

        // check if voucher is expired end_date
        if (Carbon::parse($voucher->end_date)->lt(Carbon::now())) {
            return response()->json([
                'message' => 'Voucher sudah kedaluarsa',
                'voucher_id' => null,
                'amount_discount' => 0,
            ], 400);
        }

        // check if voucher is not start_date
        if (Carbon::parse($voucher->start_date)->gt(Carbon::now())) {
            return response()->json([
                'message' => 'Voucher belum berlaku',
                'voucher_id' => null,
                'amount_discount' => 0,
            ], 400);
        }
        if ($voucher->total < 1 || $voucher->usage_for < 1) {
            return response()->json([
                'message' => 'Voucher sudah habis',
                'voucher_id' => null,
                'amount_discount' => 0,
            ], 400);
        }
        if ($user) {
            $cek_voucher = DB::table('user_vouchers')->where('voucher_id', $voucher->id)->where('user_id', $user->id)->count();
            if ($cek_voucher > $voucher->usage_for) {
                return response()->json([
                    'message' => 'Kamu sudah menggunakan voucher ini',
                    'voucher_id' => null,
                    'amount_discount' => 0,
                ], 400);
            }
        } else {
            $cek_voucher = DB::table('user_vouchers')->where('voucher_id', $voucher->id)->count();
            if ($cek_voucher > $voucher->usage_for) {
                return response()->json([
                    'message' => 'Kuota voucher sudah habis',
                    'voucher_id' => null,
                    'amount_discount' => 0,
                ], 400);
            }
        }

        if ($voucher->type == 'point') {
            if ($voucher->userVoucher()->count() < 1) {
                return response()->json([
                    'message' => 'Kamu sudah menggunakan voucher ini',
                    'voucher_id' => null,
                    'amount_discount' => 0,
                ], 400);
            }
        }

        $discount = $voucher->nominal;
        if ($voucher->percentage > 0) {
            $discount = $request->nominal * getPercentage($voucher->percentage);
        }

        if ($voucher->min > 0) {
            if ($request->nominal < $voucher->min) {
                return response()->json([
                    'message' => 'Minimal transaksi adalah Rp ' . number_format($voucher->min),
                    'voucher_id' => null,
                    'amount_discount' => 0,
                ], 400);
            }
            if ($discount > $request->nominal) {
                return response()->json([
                    'message' => 'Berhasil menggunakan voucher',
                    'voucher_id' => $voucher->id,
                    'amount_discount' => $voucher->nominal,
                ]);
            }
            return response()->json([
                'message' => 'Berhasil menggunakan voucher',
                'voucher_id' => $voucher->id,
                'amount_discount' => $discount > 0 ? $discount : $voucher->nominal,
            ]);
        }

        if ($discount > $request->nominal) {
            return response()->json([
                'message' => 'Berhasil menggunakan voucher',
                'voucher_id' => $voucher->id,
                'amount_discount' => $voucher->nominal,
            ]);
        }

        if ($request->nominal < $voucher->nominal) {
            return response()->json([
                'message' => 'Berhasil menggunakan voucher',
                'voucher_id' => $voucher->id,
                'amount_discount' => $discount > 0 ? $discount : $voucher->nominal,
            ]);
        }

        if ($request->nominal == $voucher->nominal) {
            return response()->json([
                'message' => 'Berhasil menggunakan voucher',
                'voucher_id' => $voucher->id,
                'amount_discount' => $discount > 0 ? $discount : $voucher->nominal,
            ]);
        }

        return response()->json([
            'message' => 'Berhasil menggunakan voucher',
            'voucher_id' => $voucher->id,
            'amount_discount' => $voucher->nominal ?? 0,
        ]);
    }

    public function confirmation(Request $request)
    {
        $transaction = Transaction::find($request->id_transaksi);
        $data = ['status' => 1];

        $transaction->update($data);

        // send notification
        if ($transaction) {
            createNotification(
                'TRANS21',
                [
                    'user_id' => $transaction->user_id
                ],
                [
                    'user' => $transaction->salesUser?->name ?? '-',
                    'invoice' => $transaction->id_transaksi,
                ],
                ['brand_id' => $transaction->brand_id]
            );
        }

        $dataLog = [
            'log_type' => '[fis-dev]transaction',
            'log_description' => 'Confirmation Transaction - ' . $request->id_transaksi,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status link',
        ]);
    }

    public function updateStatusLink(Request $request)
    {
        $transaction = Transaction::find($request->id_transaksi);
        $expired = $request->status_link == 1 ? Carbon::now()->addDays(1) : $request->expire_payment;
        $data = ['status_link' => $request->status_link, 'expire_payment' => $expired];

        // if ($request->status_link == 1) {
        //     $data['status'] = 0;
        //     $data['status_delivery'] = 0;
        // } else {
        //     $data['status'] = 4;
        // }

        $transaction->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status link',
        ]);
    }

    public function createOrderPopaket(Request $request)
    {
        $transaction = Transaction::find($request->transaction_id);
        CreateOrderPopaket::dispatch($transaction)->onQueue('queue-log');


        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil membuat order popaket',
        ]);
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

    public function exportBagiHasil()
    {
        $transaction = Transaction::where('status_delivery', 4)->where('status', 7)->get();

        $file_name = 'convert/FIS-Withdraw-BagiHasil-' . date('d-m-Y') . '.xlsx';

        // Excel::store(new TransactionExport($transaction), $file_name, 's3', null, [
        //     'visibility' => 'public',
        // ]);
        // return response()->json([
        //     'status' => 'success',
        //     'data' => Storage::disk('s3')->url($file_name),
        //     'message' => 'List Bagi Hasil'
        // ]);
        Excel::store(new TransactionExport($transaction), $file_name, 'public');
        return response()->json([
            'status' => 'success',
            'data' => asset('storage/' . $file_name),
            'message' => 'List Notification'
        ]);
    }

    public function getVANumber(Request $request)
    {
        $client = new Client();

        foreach ($request->products as $key => $product) {
            $variant = DB::table('product_variant_stocks')->where('warehouse_id', 3)->where('company_id', 1)->where('product_variant_id', $product['variant_id'])->first();
            if ($variant) {
                if ($variant->stock_of_market < $product['qty']) {
                    return response()->json([
                        'status' => 'error',
                        'data' => null,
                        'message' => $product['product_name'] . ' Stock Tidak Mencukupi'
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'data' => null,
                    'message' => ' Stock Tidak Mencukupi'
                ], 400);
            }
        }

        try {
            $response = $client->request('POST', getSetting('APP_API_URL') . '/transaction/create/va-number', [
                'form_params' => $request->all()
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            return response()->json([
                'status' => 'success',
                'data' => $responseJSON,
                'message' => 'Get Va Number Success'
            ]);
        } catch (ClientException $th) {
            $response = $th->getResponse();
            $responseBody = json_decode($response->getBody(), true);
            return response()->json([
                'status' => 'error',
                'data' => $th->getMessage(),
                'message' => 'Get Va Number Error'
            ], 400);
        }
    }

    public function updatePrice(Request $request, $item_id)
    {
        $trans = TransactionDetail::find($item_id);
        if ($trans) {
            $diskon = TransactionDetail::where('transaction_id', $trans->transaction_id)->where('id', '!=', $item_id)->sum('diskon');
            $discount = MasterDiscount::find($trans->discount_id);
            if ($discount) {
                $percentage = $discount->percentage > 0 ? $discount->percentage / 100 : 0;
                $subtotal = ($trans->qty * $request->item_price);
                $amount_diskon = $subtotal * $percentage;
                $amount = $diskon + $amount_diskon;
                $trans->update([
                    'diskon' => $amount_diskon,
                    'price' => $request->item_price,
                    'subtotal' => $subtotal - $amount_diskon
                ]);

                $transaction = Transaction::find($trans->transaction_id);
                $diskon_voucher = $transaction->diskon_voucher ?? 0;
                $nominal = $transaction->subtotal + $transaction->ongkir;
                $transaction->update([
                    'diskon' => $amount,
                    'nominal' => $nominal,
                    'amount_to_pay' => $nominal,
                ]);
            } else {
                $trans->update(['price' => $request->item_price, 'subtotal' => ($trans->qty * $request->item_price)]);
                $transaction = Transaction::find($trans->transaction_id);
                $diskon_voucher = $transaction->diskon_voucher ?? 0;
                $nominal = $transaction->subtotal + $transaction->ongkir;
                $transaction->update([
                    'nominal' => $nominal,
                    'amount_to_pay' => $nominal,
                ]);
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        }


        return response()->json([
            'status' => 'error',
            'message' => 'Data Gagal Disimpan'
        ], 400);
    }

    public function updateDiscount(Request $request, $item_id)
    {
        $trans = TransactionDetail::find($item_id);
        if ($trans) {
            $diskon = TransactionDetail::where('transaction_id', $trans->transaction_id)->where('id', '!=', $item_id)->sum('diskon');
            $discount = MasterDiscount::find($request->discount_id);
            if ($discount) {
                $percentage = $discount->percentage > 0 ? $discount->percentage / 100 : 0;
                $subtotal = $trans->price * $trans->qty;
                $amount_diskon = $subtotal * $percentage;
                $amount = $diskon + $amount_diskon;
                $trans->update([
                    'diskon' => $amount_diskon,
                    'discount_id' => $discount->id,
                    'subtotal' => $subtotal - $amount_diskon,
                    'data_discount' => $discount ? json_encode([
                        'id' => $discount->id,
                        'title' => $discount->title,
                        'percentage' => $discount->percentage,
                        'sales_channel' => $discount->sales_channel,
                        'sales_tag' => $discount->sales_tag,
                    ]) : null,
                ]);
                $transaction = Transaction::find($trans->transaction_id);
                $diskon_voucher = $transaction->diskon_voucher ?? 0;
                $nominal = $transaction->subtotal + $transaction->ongkir;
                $transaction->update([
                    'diskon' => $amount,
                    'nominal' => $nominal,
                    'amount_to_pay' => $nominal,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Gagal Disimpan'
        ], 400);
    }


    public function updateAdminFee(Request $request, $item_id)
    {
        $trans = Transaction::find($item_id);
        if ($trans) {
            $trans->update(['admin_fee' => $request->item_price]);
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Gagal Disimpan'
        ], 400);
    }

    public function updateDeduction(Request $request, $item_id)
    {
        $trans = Transaction::find($item_id);
        if ($trans) {
            $trans->update(['deduction' => $request->item_price]);
            return response()->json([
                'status' => 'success',
                'message' => 'Deduction Berhasil Diubah'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Deduction Gagal Diubah'
        ], 400);
    }


    public function updateResi(Request $request)
    {
        $trans = Transaction::find($request->transaction_id);
        if ($trans) {
            if ($trans->status_delivery == 5) {
                $trans->update(['returned_at' => null, 'completed_at' => null, 'delivered_at' => null, 'status_delivery' => 21, 'resi' => $request->resi]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Resi Berhasil Diubah'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Resi Gagal Diubah, Pesanan tidak di kembalikan'
            ], 400);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Resi Gagal Diubah'
        ], 400);
    }

    public function export(Request $request)
    {
        $file_name = 'convert/Transaksi-LMS-' . date('d-m-Y') . '.xlsx';

        Excel::store(new TransactionTelmarkExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'Export Success'
        ]);
    }

    public function updateAddressTransaction(Request $request)
    {
        $address = AddressUser::find($request->address_id);
        $trans = Transaction::find($request->transaction_id);
        if ($address) {
            $address->update([
                'alamat' => $request->alamat,
                'provinsi_id' => $request->provinsi_id,
                'kabupaten_id' => $request->kabupaten_id,
                'kecamatan_id' => $request->kecamatan_id,
                'kelurahan_id' => $request->kelurahan_id,
                'kodepos' => $request->kodepos,
            ]);

            if ($address) {
                $dataTransaction['final_address'] = json_encode([
                    'address_user_id' => $request->address_user_id,
                    'address' => $address?->alamat_detail,
                    'telepon' => formatPhone($address?->telepon, '+628'),
                    'name' => $address?->nama,
                    'type' => $address?->type,
                ]);

                $dataTransaction['data_user_address'] = json_encode([
                    'id' => $address->id,
                    'type' => $address->type,
                    'nama' => $address->nama,
                    'alamat_detail' => $address->alamat_detail,
                    'kodepos' => $address->kodepos,
                    'telepon' => formatPhone($address->telepon, '+628'),
                    'catatan' => $address->catatan,
                ]);
            }

            if ($request->note) {
                $dataTransaction['note'] = $request->note;
            }
            $trans->update($dataTransaction);
            $trans->shippingType()->update(['shipping_destination' => $address->kodepos]);
            return response()->json([
                'status' => 'success',
                'message' => 'Alamat Berhasil Disimpan'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Alamat Gagal Disimpan'
        ], 400);
    }
}
