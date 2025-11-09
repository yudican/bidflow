<?php

namespace App\Http\Controllers\Api\Transaction;

use App\Http\Controllers\Controller;
use App\Models\ConfirmPayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConfirmPaymentController extends Controller
{
    public function uploadPayment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'nama_rekening' => 'required',
            'bank_tujuan' => 'required',
            'bank_dari' => 'required',
            'jumlah_bayar' => 'required|numeric',
            'foto_struk' => 'required|image',
        ]);

        if ($validate->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 400,
                'message' => 'Silahkan isi semua form yang tersedia',
                'messages' => $validate->errors(),
            ];
            return response()->json($respon, 400);
        }

        if (!$request->hasFile('foto_struk')) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file('foto_struk');
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }

        $transaction = Transaction::find($request->transaction_id);

        if (!$transaction) {
            return response()->json([
                'error' => true,
                'message' => 'Transaksi Tidak Ditemukan',
                'status_code' => 400,
            ], 400);
        }

        try {
            DB::beginTransaction();
            // $file = $request->foto_struk->store('upload', 'public');
            $file = Storage::disk('s3')->put('upload/confirm_payment', $request->foto_struk, 'public');
            ConfirmPayment::create([
                'transaction_id' => $request->transaction_id,
                'nama_rekening' => $request->nama_rekening,
                'bank_tujuan' => $request->bank_tujuan,
                'bank_dari' => $request->bank_dari,
                'jumlah_bayar' => $request->jumlah_bayar,
                'tanggal_bayar' => Carbon::now(),
                'foto_struk' => $file,
                'status' => 0,
            ]);

            $transaction->update(['status' => 2]);
            $notification_data = [
                'user' => $transaction->user->name,
                'invoice' => $transaction->id_transaksi,
                'payment_method' => $transaction->paymentMethod->bank_name,
                'rincian_bayar' => getRincianPembayaran($transaction),
                'rincian_transaksi' => getRincianTransaksi($transaction),
                'bank_account_number' => $transaction->paymentMethod->bank_account_number,
                'bank_account_name' => $transaction->paymentMethod->bank_account_name,
            ];

            createNotification('UUPP200', [], $notification_data, ['transaction_id' => $transaction->id]);
            createNotification('UCPA200', [], $notification_data, ['transaction_id' => $transaction->id]);
            createNotification('UCPAM200', ['user_id' => $transaction->user_id, 'other_id' => $transaction->id], [], ['transaction_id' => $transaction->id]);

            DB::commit();
            return response()->json([
                'error' => false,
                'message' => 'Berhasil, Bukti Bayar Berhasil Disimpan',
                'status_code' => 200,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => true,
                'message' => 'Terjadi Kesalahan, Bukti Bayar Gagal Disimpan',
                'dev_message' => $th->getMessage(),
                'status_code' => 400,
            ], 400);
        }
    }
}
