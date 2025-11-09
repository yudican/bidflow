<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransactionAgent;
use App\Models\Transaction;
use App\Models\TransactionDeliveryStatus;
use App\Models\TransactionStatus;
use App\Models\LogApproveFinance;
use DB;
use Illuminate\Support\Facades\Auth;

class TransAgentController extends Controller
{
    public function index($trans_id = null)
    {
        return view('spa.spa-index');
    }

    public function listTransAgentAll(Request $request)
    {
        $user = Auth::user();

        $id_transaksi = $request->id_transaksi;
        $tanggal_transaksi = $request->tanggal_transaksi;
        $account_id = $request->account_id;
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));

        if ($id_transaksi) {
            $transaction->where('id_transaksi', $id_transaksi);
        }

        if ($tanggal_transaksi) {
            $transaction->whereBetween('created_at', $tanggal_transaksi);
        }

        // cek switch account
        if ($account_id) {
            if($account_id != 'null'){
                $transaction->where('company_id', $account_id);
            }
        }

        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->where('user_id', auth()->user()->id);
        }

        $transactions = $transaction->orderBy('created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function listTransAgentWaitingPayment(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->whereIn('status', [1])->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->whereIn('status', [1])->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function confirmation(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->where('status', 2)->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->where('status', 2)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function newTransaction(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->whereIn('status', [3])->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->where('status', 3)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function warehouse(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod', 'label'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->where('status', 7)->where('status_delivery', 1)->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->where('status', 7)->where('status_delivery', 1)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function readyProduct(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->whereIn('status', [3, 7])->where('status_delivery', 21)->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->whereIn('status', [3, 7])->where('status_delivery', 21)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function delivery(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->where('status_delivery', 3)->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->where('status_delivery', 3)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function orderAccepted(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->where('status_delivery', 4)->where('status', 7)->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->where('status_delivery', 4)->where('status', 7)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $transaction = TransactionAgent::query()->with(array('user', 'paymentMethod'));
        if (in_array($user->role->role_type, ['agent', 'subagent'])) {
            return $transaction->where('status_delivery', '>', 3)->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
        }
        $transactions = $transaction->where('status_delivery', '>', 3)->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function detailTransAgent($id)
    {
        $detail =  TransactionAgent::with(['transactionDetail', 'transactionDetail.product', 'brand', 'user', 'shippingType', 'voucher'])->where('id', $id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $detail
        ]);
    }

    public function assignWarehouse($id)
    {
        $transaction = TransactionAgent::find($id);
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

                LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction->id, 'keterangan' => 'Assign Warehouse']);
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
                return response()->json([
                    'status' => 'Data Berhasil Disimpan',
                ]);
            } catch (ClientException $th) {
                $response = $th->getResponse();
                DB::rollBack();
                return response()->json([
                    'status' => 'Data Gagal Disimpan',
                ]);
            }
        }
    }

    public function packingProcess($id)
    {
        $transaction = TransactionAgent::find($id);
        if ($transaction) {
            $transaction->update(['status_delivery' => 21]);
            TransactionDeliveryStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'delivery_status' => 21,
            ]);
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $id, 'keterangan' => 'Packing Process']);
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
            return response()->json([
                'status' => 'Proses Pengemasan Berhasil',
            ]);
        }
        return response()->json([
            'status' => 'Proses Pengemasan Gagal',
        ]);
    }

    public function productReceived($id)
    {
        $transaction = TransactionAgent::find($id);
        if ($transaction) {
            $transaction->update(['status_delivery' => 4]);
            TransactionDeliveryStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'delivery_status' => 4,
            ]);
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $id, 'keterangan' => 'Product Received']);
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
            return response()->json([
                'status' => 'Pesanan Diterima Berhasil',
            ]);
        }
        return response()->json([
            'status' => 'Pesanan Diterima Gagal',
        ]);
    }

    public function bulkInvoice(Request $request)
    {
        if (count($request->data) > 0) {
            $selected = $request->data;
            $urls = [];
            foreach ($selected as $value) {
                $urls[] = route('invoice.print.agent', $value);
            }

            // print_invoice($urls);
            return response()->json([
                'status' => 'success',
                'data' => $urls
            ]);
        }
    }

    // public function bulkPackingProcess(Request $request)
    // {
    //     foreach ($request->data as $value) {
    //         $this->emit('packingProcess', $value);
    //     }
    // }
}
