<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index($payment_method_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPaymentMethod(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $payment_type = $request->payment_type;
        $payment_channel = $request->payment_channel;

        $banner =  PaymentMethod::query();
        if ($search) {
            $banner->where(function ($query) use ($search) {
                $query->where('nama_bank', 'like', "%$search%");
                $query->orWhere('nomor_rekening_bank', 'like', "%$search%");
                $query->orWhere('nama_rekening_bank', 'like', "%$search%");
                $query->orWhere('payment_type', 'like', "%$search%");
                $query->orWhere('payment_channel', 'like', "%$search%");
            });
        }

        if ($status) {
            $banner->where('status', $status == 10 ? 0 : $status);
        }

        if ($payment_type) {
            $banner->where('payment_type', $payment_type);
        }

        if ($payment_channel) {
            $banner->where('payment_channel', $payment_channel);
        }

        $banners = $banner->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $banners,
            'message' => 'List Payment Method'
        ]);
    }

    public function getParentsData()
    {
        $parents = PaymentMethod::whereNull('parent_id')->whereStatus(1)->get();
        return response()->json([
            'status' => 'success',
            'data' => $parents,
            'message' => 'List Payment Method'
        ]);
    }


    public function getDetailPaymentMethod($payment_method_id)
    {
        $brand = PaymentMethod::with('parent')->find($payment_method_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Payment Method'
        ]);
    }

    public function savePaymentMethod(Request $request)
    {
        try {
            DB::beginTransaction();
            $logo_bank = $this->uploadImage($request, 'logo_bank');
            $data = [
                'nama_bank'  => $request->nama_bank,
                'nomor_rekening_bank'  => $request->nomor_rekening_bank,
                'logo_bank'  => $logo_bank,
                'nama_rekening_bank'  => $request->nama_rekening_bank,
                'parent_id'  => $request->parent_id,
                'payment_type'  => $request->payment_type,
                'payment_channel'  => $request->payment_channel,
                'payment_code'  => $request->payment_code,
                'payment_va_number'  => $request->payment_va_number,
                'status'  => $request->status
            ];

            $payment = PaymentMethod::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_payment_method',
                'log_description' => 'Create Master Payment Method - ' . $payment->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Metode Pembayaran berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Metode Pembayaran Gagal Disimpan'
            ], 400);
        }
    }

    public function updatePaymentMethod(Request $request, $payment_method_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'nama_bank'  => $request->nama_bank,
                'nomor_rekening_bank'  => $request->nomor_rekening_bank,
                'nama_rekening_bank'  => $request->nama_rekening_bank,
                'parent_id'  => $request->parent_id,
                'payment_type'  => $request->payment_type,
                'payment_channel'  => $request->payment_channel,
                'payment_code'  => $request->payment_code,
                'payment_va_number'  => $request->payment_va_number,
                'status'  => $request->status
            ];
            $row = PaymentMethod::find($payment_method_id);

            if ($request->logo_bank) {
                $logo_bank = $this->uploadImage($request, 'logo_bank');
                $data = ['logo_bank' => $logo_bank];
                if (Storage::exists('public/' . $request->logo_bank)) {
                    Storage::delete('public/' . $request->logo_bank);
                }
            }

            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_payment_method',
                'log_description' => 'Update Master Payment Method - ' . $payment_method_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Metode Pembayaran berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Metode Pembayaran Gagal Disimpan'
            ], 400);
        }
    }

    public function deletePaymentMethod($payment_method_id)
    {
        $data = PaymentMethod::find($payment_method_id);
        $data->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_payment_method',
            'log_description' => 'Delete Master Payment Method - ' . $payment_method_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Metode Pembayaran berhasil dihapus'
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
        $file = Storage::disk('s3')->put('upload/master/payment_method', $request[$path], 'public');
        return $file;
    }
}
