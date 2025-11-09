<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\SkuMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class SkuController extends Controller
{
    public function index($sku_id = null)
    {
        return view('spa.spa-index');
    }

    public function listSku(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $package = $request->package_id;

        $sku =  SkuMaster::query();
        if ($search) {
            $sku->where(function ($query) use ($search) {
                $query->where('sku', 'like', "%$search%");
            });
        }

        // if ($status >= 0) {
        //     $sku->whereIn('status', $status);
        // }

        if ($package) {
            $sku->where('package_id', $package);
        }


        $variants = $sku->orderBy('created_at', 'desc')->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $variants,
            'message' => 'List SkuMaster'
        ]);
    }


    public function getDetailSku($sku_id)
    {
        $brand = SkuMaster::find($sku_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail SkuMaster'
        ]);
    }

    public function saveSku(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'sku'  => $request->sku,
                'package_id'  => $request->package_id,
                'expired_at'  => $request->expired_at,
            ];

            $sku = SkuMaster::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_sku',
                'log_description' => 'Create Master Sku - ' . $request->sku,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data SkuMaster Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data SkuMaster Gagal Disimpan'
            ], 400);
        }
    }

    public function updateSku(Request $request, $sku_id)
    {
        
        try {
            DB::beginTransaction();
            $data = [
                'sku'  => $request->sku,
                'package_id'  => $request->package_id,
                'expired_at'  => $request->expired_at,
            ];

            if ($request->status != '') {
                $data['status'] = $request->status;
            }

            $row = SkuMaster::find($sku_id);
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_sku',
                'log_description' => 'Update Master Sku - ' . $request->sku,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data SkuMaster Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data SkuMaster Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteSku($sku_id)
    {
        $banner = SkuMaster::find($sku_id);
        $banner->delete();

        $dataLog = [
            'log_type' => '[fis-dev]master_sku',
            'log_description' => 'Delete Master Sku - ' . $sku_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Data SkuMaster berhasil dihapus'
        ]);
    }
}
