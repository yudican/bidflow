<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Jobs\GetDeviceIdFromUserQueue;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Str;

class VoucherController extends Controller
{
    public function index($voucher_id = null)
    {
        return view('spa.spa-index');
    }

    public function listVoucher(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $voucher =  Voucher::query();
        if ($search) {
            $voucher->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%");
                $query->orWhere('voucher_code', 'like', "%$search%");
            });
        }

        if ($status) {
            $voucher->whereIn('status', $status);
        }


        $vouchers = $voucher->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $vouchers,
            'message' => 'List Voucher'
        ]);
    }


    public function getDetailVoucher($voucher_id)
    {
        $brand = Voucher::with('brands')->find($voucher_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Voucher'
        ]);
    }

    public function saveVoucher(Request $request)
    {
        $voucherCek = Voucher::where('voucher_code', $request->voucher_code)->first();
        if ($voucherCek) {
            return response()->json([
                'status' => 'success',
                'message' => 'Maaf, Kode Voucher yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $image = $this->uploadImage($request, 'image');
            $data = [
                'voucher_code'  => $request->voucher_code ?? Str::random(10),
                'title'  => $request->title,
                'nominal'  => $request->nominal,
                'percentage'  => $request->percentage,
                'total'  => $request->total,
                'slug'  => Str::slug($request->title, '-'),
                'description'  => $request->description,
                'image'  => $image,
                'start_date'  => $request->start_date,
                'end_date'  => $request->end_date,
                'min'  => $request->min,
                'type'  => $request->type,
                'total_point'  => $request->total_point,
                'usage_for'  => $request->usage_for,
                'status'  => $request->status
            ];

            $voucher = Voucher::create($data);
            $voucher->brands()->sync(json_decode($request->brand_id, true));


            GetDeviceIdFromUserQueue::dispatch($request->title)->onQueue('queue-backend');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Voucher Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Voucher Gagal Disimpan'
            ], 400);
        }
    }

    public function updateVoucher(Request $request, $voucher_id)
    {
        $voucherCek = Voucher::where('voucher_code', $request->voucher_code)->where('id', '!=', $voucher_id)->first();
        if ($voucherCek) {
            return response()->json([
                'status' => 'success',
                'message' => 'Maaf, Kode Voucher yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $data = [
                'voucher_code'  => $request->voucher_code ?? Str::random(10),
                'title'  => $request->title,
                'slug'  => Str::slug($request->title, '-'),
                'nominal'  => $request->nominal,
                'percentage'  => $request->percentage,
                'total'  => $request->total,
                'description'  => $request->description,
                'start_date'  => $request->start_date,
                'end_date'  => $request->end_date,
                'min'  => $request->min,
                'type'  => $request->type,
                'total_point'  => $request->total_point,
                'usage_for'  => $request->usage_for,
                'status'  => $request->status
            ];

            $row = Voucher::find($voucher_id);

            if ($request->image) {
                $image = $this->uploadImage($request, 'image');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $request->image)) {
                    Storage::delete('public/' . $request->image);
                }
            }
            $row->update($data);
            if ($request->brand_id) {
                $row->brands()->sync(json_decode($request->brand_id, true));
            }

            $dataLog = [
                'log_type' => '[fis-dev]master_vourcher',
                'log_description' => 'Update Master Voucher - ' . $voucher_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Voucher Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Voucher Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function deleteVoucher($voucher_id)
    {
        $voucher = Voucher::find($voucher_id);
        $voucher->delete();

        $dataLog = [
            'log_type' => '[fis-dev]master_vourcher',
            'log_description' => 'Delete Master Voucher - ' . $voucher_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Data Voucher berhasil dihapus'
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

        try {
            $file = Storage::disk('s3')->put('upload/master/voucher', $request[$path], 'public');
            return $file;
        } catch (\Throwable $th) {
            setSetting('upload_voucher_error_message_a', $th->getTraceAsString());
            return null;
        }
    }
}
