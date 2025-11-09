<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\VoucherOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Str;

class VoucherOngkirController extends Controller
{
    public function index($voucher_id = null)
    {
        return view('spa.spa-index');
    }

    public function listVoucher(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $voucher =  VoucherOngkir::query();
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
        $brand = VoucherOngkir::with('brands')->find($voucher_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Voucher'
        ]);
    }

    public function saveVoucher(Request $request)
    {
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

            $voucher = VoucherOngkir::create($data);
            $voucher->brands()->sync(json_decode($request->brand_id, true));

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

            $row = VoucherOngkir::find($voucher_id);

            if ($request->image) {
                $image = $this->uploadImage($request, 'image');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $request->image)) {
                    Storage::delete('public/' . $request->image);
                }
            }
            $row->update($data);
            $row->brands()->sync(json_decode($request->brand_id, true));

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
        $voucher = VoucherOngkir::find($voucher_id);
        $voucher->delete();
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
        $file = Storage::disk('s3')->put('upload/master/voucher', $request[$path], 'public');
        return $file;
    }
}
