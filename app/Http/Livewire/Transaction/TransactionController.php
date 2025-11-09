<?php

namespace App\Http\Livewire\Transaction;

use App\Exports\TransactionExportTable;
use App\Http\Controllers\Shipping\PopaketController;
use App\Jobs\AssignToWarehouse;
use App\Jobs\CreateOrderPopaket;
use App\Jobs\GetOrderResi;
use App\Models\Transaction;
use App\Models\UserPoint;
use App\Models\ConfirmPayment;
use App\Models\InventoryItem;
use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\MasterPoint;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantBundlingStock;
use App\Models\ProductVariantStock;
use App\Models\TransactionAgent;
use App\Models\TransactionDeliveryStatus;
use App\Models\TransactionStatus;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class TransactionController extends Component
{

    public $transaction_id;
    public $order;
    public $payment;
    public $trans;
    public $status;
    public $keterangan;
    public $status_transaksi = '';
    public $resi;
    public $logdata;

    public $jumlah_bayar;
    public $bank_dari;
    public $nama_rekening;
    public $tanggal_bayar;
    public $ref_id;



    public $route_name = null;
    public $segment = null;
    public $segment1 = null;
    public $segment2 = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    // report
    public $reportrange;
    public $selectedRow = [];
    public $logs = [];

    // history shipping
    public $history_shipping = null;

    protected $listeners = ['getDataTransactionById', 'getTransactionId', 'showPaymentDetail', 'showPhoto', 'packingProcess', 'inputResi', 'assignWarehouse', 'productReceived', 'getSelected', 'logTransaction', 'showTimeline'];

    public function mount()
    {
        $this->status_transaksi = request()->segment(2);
        if (in_array(auth()->user()->role->role_type, ['agent', 'subagent'])) {
            $route_path = [
                'history-agent',
                'waiting-agent',
                'approve-agent',
                'proccess-agent'
            ];
            if (in_array(request()->segment(3), $route_path)) {
                $this->status_transaksi = request()->segment(3);
            } else {
                $this->status_transaksi = request()->segment(2);
            }
        }
        $this->route_name = request()->route()->getName();
        $this->segment1 = request()->segment(1);
        $this->segment2 = request()->segment(2);
        $this->segment = request()->segment(2);
        if (request()->segment(2) == 'agent-proccess') {
            $this->status_transaksi = request()->segment(3);
            $this->segment2 = request()->segment(3);
        }
    }

    public function showTimeline($data = null)
    {
        $this->history_shipping = $data;
        $this->emit('timelineModal', 'show');
    }

    public function render()
    {
        $transaction = Transaction::all();

        return view('livewire.transaction.tbl-transactions', [
            'items' => $transaction
        ]);
    }

    public function approvePayment()
    {
        try {
            DB::beginTransaction();
            if ($this->payment) {
                if (!$this->payment->ref_id) {
                    $this->validate(['ref_id' => 'required']);
                }
                $transaction = $this->getTransaction($this->payment->transaction_id);
                if ($transaction) {
                    // $resi = $this->generateCode($transaction);
                    CreateOrderPopaket::dispatch($transaction)->onQueue('queue-log');
                    // $popaket->createShippingOrderValidateToken($transaction);
                    $transaction->update(['status' => 3]);
                    TransactionStatus::create([
                        'id_transaksi' => $transaction->id_transaksi,
                        'status' => 3,
                    ]);

                    $masterPoint = MasterPoint::limit(1)->get();
                    foreach ($masterPoint as $point) {
                        if ($point->type == 'transaction') {
                            if ($transaction->nominal >= $point->min_trans && $transaction->nominal <= $point->max_trans) {
                                UserPoint::create([
                                    'user_id' => $transaction->user_id,
                                    'point' => $point->point
                                ]);
                            }
                        } else {
                            UserPoint::create([
                                'user_id' => $transaction->user_id,
                                'point' => $transaction->transactionDetail->count() * $point->point
                            ]);
                        }
                    }

                    $transaction->confirmPayment->update(['status' => 1, 'ref_id' => $this->ref_id]);
                    //log approval
                    LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->payment->transaction_id, 'keterangan' => 'Approve Finance']);

                    foreach ($transaction->transactionDetail as $trans) {
                        if ($trans->product) {
                            $this->updateStock($trans, $transaction->warehouse_id);
                            // $trans->product()->update(['stock' => $trans->product->stock - $trans->qty]);
                            // $stock = ProductStock::where('product_id', $trans->product_id)->where('warehouse_id', $transaction->warehouse_id)->first();

                            // if ($stock) {
                            //     $stock->update(['stock' => $stock->stock - $trans->qty]);
                            // }
                            // if ($trans->product->stock <= 5) {
                            //     // create notification
                            //     $data_notification = [
                            //         'title' => 'Stock Produk < 5',
                            //         'body' => 'Stock Produk kurang dari 5'
                            //     ];
                            //     createNotification($data_notification, 'member'); // role dapat dari tabel role (admin, member, dll) field role_type

                            // }
                        }
                    }

                    $user = $transaction->user;
                    $data_notification_admin = [
                        'invoice' => $transaction->id_transaksi,
                        'name' => auth()->user()->name,
                    ];
                    createNotification('APP200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);

                    $notification_data = [
                        'name' => $user->name,
                        'rincian_bayar' => getRincianPembayaran($transaction),
                        'rincian_transaksi' => getRincianTransaksi($transaction),
                    ];
                    createNotification('TRS200', ['user_id' => $user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);

                    $this->_reset();
                    return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
                }
            } elseif ($this->trans) {
                $rule = [
                    'nama_rekening'  => 'required',
                    'bank_dari'  => 'required',
                    'jumlah_bayar'  => 'required',
                    'tanggal_bayar'  => 'required',
                    'ref_id'  => 'required'
                ];
                $this->validate($rule);
                $transaction = $this->getTransaction($this->trans->id);
                if ($transaction) {
                    // $this->generateCode($transaction);
                    // $popaket->createShippingOrderValidateToken($transaction);
                    CreateOrderPopaket::dispatch($transaction)->onQueue('queue-log');
                    $transaction->update(['status' => 3]);
                    TransactionStatus::create([
                        'id_transaksi' => $transaction->id_transaksi,
                        'status' => 3,
                    ]);
                    $masterPoint = MasterPoint::limit(1)->get();

                    foreach ($masterPoint as $point) {
                        if ($point->type == 'transaction') {
                            if ($transaction->nominal >= $point->min_trans && $transaction->nominal <= $point->max_trans) {
                                UserPoint::create([
                                    'user_id' => $transaction->user_id,
                                    'point' => $point->point
                                ]);
                            }
                        } else {
                            UserPoint::create([
                                'user_id' => $transaction->user_id,
                                'point' => $transaction->transactionDetail->count() * $point->point
                            ]);
                        }
                    }
                    ConfirmPayment::create([
                        'transaction_id' => $this->trans->id,
                        'nama_rekening' => $this->nama_rekening,
                        'bank_dari' => $this->bank_dari,
                        'jumlah_bayar' => $this->jumlah_bayar,
                        'tanggal_bayar' => Carbon::now(),
                        'status' => 1,
                        'ref_id' => $this->ref_id
                    ]);
                    //log approval
                    LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->trans->id, 'keterangan' => 'Approve Finance']);
                    // create notification
                    $data_notification_admin = [
                        'invoice' => $transaction->id_transaksi,
                        'name' => auth()->user()->name,
                    ];
                    createNotification('APP200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
                    $notification_data = [
                        'name' => $transaction->user->name,
                        'rincian_bayar' => getRincianPembayaran($transaction),
                        'rincian_transaksi' => getRincianTransaksi($transaction),
                    ];
                    createNotification('TRS200', ['user_id' => $transaction->user_id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);

                    foreach ($transaction->transactionDetail as $trans) {
                        if ($trans->product) {
                            // $trans->product()->update(['stock' => $trans->product->stock - $trans->qty]);
                            // $stock = ProductStock::where('product_id', $trans->product_id)->where('warehouse_id', $transaction->warehouse_id)->first();

                            // if ($stock) {
                            //     $stock->update(['stock' => $stock->stock - $trans->qty]);
                            // }
                            $this->updateStock($trans, $transaction->warehouse_id);
                        }
                    }
                }
            }
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function declinePayment()
    {
        try {
            DB::beginTransaction();
            $transaction = $this->getTransaction($this->transaction_id);
            if ($transaction) {
                $transaction->update(['status' => 6]);
                TransactionStatus::create([
                    'id_transaksi' => $transaction->id_transaksi,
                    'status' => 6,
                ]);
                if ($transaction->confirmPayment) {
                    $transaction->confirmPayment->update(['status' => 2]);
                }
                //log approval
                LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->transaction_id, 'keterangan' => 'Reject Finance']);
                // create notification
                $data_notification_admin = [
                    'invoice' => $transaction->id_transaksi,
                    'user' => auth()->user()->name,
                ];
                createNotification('APP400', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
                $notification_data = [
                    'name' => $transaction->user->name,
                    'rincian_bayar' => getRincianPembayaran($transaction),
                    'rincian_transaksi' => getRincianTransaksi($transaction),
                ];
                createNotification('TRXP400', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);

                $this->_reset();
                DB::commit();
            }

            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function _validate()
    {
        $rule = [
            'status'  => 'required',
            'keterangan'  => 'required',
        ];

        return $this->validate($rule);
    }

    public function getDataTransactionById($transaction_id)
    {
        $this->_reset(false);
        $row = $this->getTransaction($transaction_id);
        $this->emit('showModal');
        $this->transaction_id = $row->id;
        $this->order = $row;
        $getUser = LogApproveFinance::leftjoin('users', 'users.id', '=', 'log_approve_finance.user_id')
            ->select('users.name')->where('log_approve_finance.transaction_id', $row->id)->orderby('log_approve_finance.created_at', 'desc')->first();
        $this->logdata = $getUser;
        $this->update_mode = true;
    }

    public function showPaymentDetail($transaction_id)
    {
        $row = $this->getTransaction($transaction_id);
        $this->transaction_id = $row->id;
        $this->payment = $row->confirmPayment;
        $this->trans = $row;
        $this->jumlah_bayar = $row->nominal;
        $this->tanggal_bayar = '01/01/2022';
        $this->emit('showModalPayment');
        $this->update_mode = true;
    }

    public function showPhoto($transaction_id)
    {
        $row = $this->getTransaction($transaction_id);
        $this->transaction_id = $row->id;
        $this->payment = $row->confirmPayment;
        $this->trans = $row;
        $this->jumlah_bayar = $row->nominal;
        $this->tanggal_bayar = '01/01/2022';
        $this->emit('showModalPhoto');
        $this->update_mode = true;
    }

    public function assignWarehouse($transaction_id)
    {
        $transaction = $this->getTransaction($transaction_id);
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

                //log approval
                LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction->id, 'keterangan' => 'Assign Warehouse']);
                // create notification
                $data_notification_admin = [
                    'user' => auth()->user()->name,
                    'rincian_bayar' => getRincianPembayaran($transaction),
                    'rincian_transaksi' => getRincianTransaksi($transaction),
                ];
                createNotification('WPO200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
                $notification_data = [
                    'user' => $transaction->user->name,
                    'invoice' => $transaction->id_transaksi,
                ];
                createNotification('ODP200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
                DB::commit();
                return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
            } catch (ClientException $th) {
                $response = $th->getResponse();
                LogError::updateOrCreate(['id' => 1], [
                    'message' => $th->getMessage(),
                    'trace' => $response->getBody()->getContents(),
                    'action' => 'Assign To warehouse (' . $transaction->id_transaksi . ')',
                ]);
                DB::rollBack();
                return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
            }
        }
        $this->_reset();
    }

    public function packingProcess($transaction_id)
    {
        $transaction = $this->getTransaction($transaction_id);
        if ($transaction) {
            $transaction->update(['status_delivery' => 21]);
            TransactionDeliveryStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'delivery_status' => 21,
            ]);
            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction_id, 'keterangan' => 'Packing Process']);
            // create notification
            $data_notification_admin = [
                'invoice' => $transaction->id_transaksi,
                'resi' => $transaction->resi,
                'rincian_transaksi' => getRincianTransaksi($transaction),
            ];
            createNotification('OPP200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
            $notification_data = [
                'user' => $transaction->user->name,
                'invoice' => $transaction->id_transaksi,
            ];
            createNotification('ODPP200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        }
        $this->_reset();
        return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
    }

    public function inputResi($transaction_id)
    {
        $row = $this->getTransaction($transaction_id);
        $this->transaction_id = $row->id;
        $this->payment = $row->confirmPayment;
        $this->resi = $row->resi;
        $this->emit('showModalResi');
        $this->update_mode = true;
    }

    public function logTransaction($transaction_id)
    {
        $row = $this->getTransaction($transaction_id);
        // dd($row->logs, $row);
        $this->transaction_id = $row->id;
        $this->logs = $row->logs;
        $this->emit('showModalLog');
        $this->update_mode = true;
    }

    public function saveResi()
    {
        $transaction = $this->getTransaction($this->transaction_id);
        if ($transaction) {
            $transaction->update(['status_delivery' => 3, 'resi' => $this->resi]);
            TransactionDeliveryStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'delivery_status' => 3,
            ]);
            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->transaction_id, 'keterangan' => 'Input Resi']);
            // create notification
            $data_notification_admin = [
                'invoice' => $transaction->id_transaksi,
                'resi' => $this->resi,
                'user' => auth()->user()->name,
                'rincian_bayar' => getRincianPembayaran($transaction),
                'rincian_transaksi' => getRincianTransaksi($transaction),
            ];
            createNotification('OIR200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
            createNotification('UUIR200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
            $notification_data = [
                'user' => @$transaction->user->name,
                'phone' => @$transaction->brand->phone,
                'email' => @$transaction->brand->email,
                'rincian_transaksi' => getRincianTransaksi($transaction),
            ];
            createNotification('RES200', ['user_id' => @$transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        }
        $this->_reset();
        return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
    }

    public function productReceived($transaction_id)
    {
        $transaction = $this->getTransaction($transaction_id);
        if ($transaction) {
            $transaction->update(['status_delivery' => 4]);
            TransactionDeliveryStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'delivery_status' => 4,
            ]);
            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction_id, 'keterangan' => 'Product Received']);
            // create notification
            $data_notification_admin = [
                'invoice' => $transaction->id_transaksi,
                'date' => date('l,d M Y | H:i')
            ];
            createNotification('OUR200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
            $notification_data = [
                'user' => $transaction->user->name,
                'invoice' => $transaction->id_transaksi,
                'brand' => $transaction->brand->name,
            ];
            createNotification('ODS200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
            createNotification('TRXR200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        }
        $this->_reset();
        return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
    }

    public function _reset($status = true)
    {
        $this->emit('refreshTable');
        $this->emit('closeModal');
        $this->transaction_id = null;
        if ($status) {
            $this->order = null;
            $this->logdata = null;
            $this->payment = null;
        }

        $this->status = null;

        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }

    public function pdf()
    {
        $data = Transaction::all();

        $pdf = PDF::loadView('documents.transaction', ['data' => $data]);

        return $pdf->download('user.pdf');

        // return $pdf->stream();
    }

    public function getSelected($data = [])
    {
        $this->selectedRow = $data;
    }

    public function applyFilterDate($value)
    {
        $this->reportrange = $value;
        $date = explode(' - ', $value);
        $start = date('Y-m-d', strtotime($date[0]));
        $end = date('Y-m-d', strtotime($date[1]));
        $this->emit('applyFilter', [$start, $end]);
    }

    public function generateCode($transaction, $cod = false)
    {
        $client = new Client();
        if ($transaction) {
            try {
                $response = $client->request('POST', getSetting('JNE_BASE_URL') . '/generatecnote', [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'form_params' => [
                        'username' => getSetting('JNE_USERNAME'),
                        'api_key' => getSetting('JNE_APIKEY'),
                        'OLSHOP_CUST' => 20010704,
                        'OLSHOP_BRANCH' => 'CGK000',
                        'OLSHOP_ORDERID' => $transaction->id . rand(123, 9999),
                        'OLSHOP_SHIPPER_NAME' => $transaction->brand->name,
                        'OLSHOP_SHIPPER_ADDR1' => substr($transaction->brand->address, 0, 30),
                        'OLSHOP_SHIPPER_ADDR2' => $transaction->brand->city_name,
                        'OLSHOP_SHIPPER_CITY' => $transaction->brand->province_name,
                        'OLSHOP_SHIPPER_REGION' => $transaction->brand->province_name,
                        'OLSHOP_SHIPPER_ZIP' => $transaction->brand->zip_code,
                        'OLSHOP_SHIPPER_PHONE' => $transaction->brand->phone,
                        'OLSHOP_RECEIVER_NAME' => $transaction->addressUser->nama,
                        'OLSHOP_RECEIVER_ADDR1' => substr($transaction->addressUser->alamat, 0, 30),
                        'OLSHOP_RECEIVER_ADDR2' => $transaction->addressUser->city_name,
                        'OLSHOP_RECEIVER_ADDR3' => $transaction->addressUser->province_name,
                        'OLSHOP_RECEIVER_CITY' => $transaction->addressUser->city_name,
                        'OLSHOP_RECEIVER_REGION' => $transaction->addressUser->province_name,
                        'OLSHOP_RECEIVER_ZIP' => $transaction->addressUser->zip_code,
                        'OLSHOP_RECEIVER_PHONE' => $transaction->addressUser->telepon,
                        'OLSHOP_QTY' => $transaction->transactionDetail->count(),
                        'OLSHOP_WEIGHT' => 1,
                        'OLSHOP_GOODSDESC' => 'Barang',
                        'OLSHOP_GOODSVALUE' => $transaction->nominal,
                        'OLSHOP_GOODSTYPE' => 'Y',
                        'OLSHOP_INS_FLAG' => 'N',
                        'OLSHOP_ORIGIN' => $transaction->shippingType->shipping_origin,
                        'OLSHOP_DEST' => $transaction->shippingType->shipping_destination,
                        'OLSHOP_SERVICE' => $transaction->shippingType->shipping_type_code,
                        'OLSHOP_COD_FLAG' => $cod ? 'Y' : 'N',
                        'OLSHOP_COD_AMOUNT' => $cod ? $transaction->nominal : 0,
                    ]
                ]);

                $responseJSON = json_decode($response->getBody(), true);
                $respon = [
                    'error' => false,
                    'status_code' => 200,
                    'message' => 'Berhasil',
                    'data' => isset($responseJSON['detail']) ? $responseJSON['detail'][0] : null,
                ];

                if (isset($responseJSON['detail'])) {
                    if (count($responseJSON['detail']) > 0) {
                        $detail = $responseJSON['detail'][0];
                        if ($detail['status'] == 'sukses') {
                            $transaction->update([
                                'resi' => $detail['cnote_no']
                            ]);
                        }
                    }
                }
                return $respon;
            } catch (\Throwable $th) {
                $respon = [
                    'error' => true,
                    'status_code' => 400,
                    'message' => 'Gagal',
                    'dev_message' => $th->getMessage(),
                ];
                return $respon;
            }
        }

        return [];
    }


    public function getTransaction($transaction_id)
    {
        if ($this->segment == 'agent-proccess') {
            return TransactionAgent::find($transaction_id);
        }

        return Transaction::find($transaction_id);
    }

    public function updateStock($trans, $warehouse_id)
    {
        try {
            DB::beginTransaction();
            $product = ProductVariant::find($trans->product_id);
            $stockInventories = ProductStock::where('warehouse_id', $warehouse_id)->where('product_id', $product->product_id)->where('stock', '>', 0)->orderBy('created_at', 'asc')->get();
            $product_variants = ProductVariant::where('product_id', $product->product_id)->get();
            foreach ($product_variants as $key => $variant) {
                $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                $qty = $variant->qty_bundling * $trans->qty;
                foreach ($variant_stocks as $key => $stock) {
                    $stok = $stock->qty;
                    $temp = $stok - $qty;
                    $temp = $temp < 0 ? 0 : $temp;
                    $stock_of_market = $stock->stock_of_market - $trans->qty;
                    $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
                    if ($temp >= 0) {
                        $stock->update(['qty' => $temp, 'stock_of_market' => $stock_of_market]);
                        break;
                    } else {
                        $stock->update(['qty' => 0, 'stock_of_market' => 0]);
                        $qty = $qty - $stok;
                    }
                }
            }

            // update stock bundling
            $bundlings = ProductVariantBundling::where('product_variant_id', $product->id)->get();
            foreach ($bundlings as $key => $bundling) {
                $bundling_stocks = ProductVariantBundlingStock::where('product_variant_bundling_id', $bundling->id)->where('warehouse_id', $warehouse_id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                $qty = $bundling->product_qty * $trans->qty;
                foreach ($bundling_stocks as $key => $stock) {
                    $stok = $stock->qty;
                    $temp = $stok - $qty;
                    $temp = $temp < 0 ? 0 : $temp;
                    $stock_off_market = $stock->stock_off_market - $trans->qty;
                    $stock_off_market = $stock_off_market < 0 ? 0 : $stock_off_market;
                    if ($temp >= 0) {
                        $stock->update(['qty' => $temp, 'stock_off_market' => $stock_off_market]);
                    } else {
                        $stock->update(['qty' => 0, 'stock_off_market' => 0]);
                        $qty = $qty - $stok;
                    }
                }
            }

            $qty = $trans->qty;
            foreach ($stockInventories as $key => $stock) {
                $stok = $stock->stock;
                $temp = $stok - $qty;
                if ($temp >= 0) {
                    $stock->update(['stock' => $temp]);
                    break;
                } else {
                    $stock->update(['stock' => 0]);
                    $qty = $qty - $stok;
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }

    public function export()
    {
        return Excel::download(new TransactionExportTable([], []), 'data-transactions.xlsx');
    }
}
