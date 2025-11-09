<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\MasterPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class MasterPointController extends Controller
{
    public function index($master_point_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterPoint(Request $request)
    {
        
        $search = $request->search;
        $brand_id = $request->brand_id;
        $type = $request->type;

        $row = MasterPoint::with('product');
        if ($search) {
            $searchMap = [
                'Per' => '',
                'Per Transaction' => 'transaction',
                'Per Product' => 'product',
            ]; 
        
            $search = $searchMap[$search] ?? $search;
        
            $row->where(function ($query) use ($search) {
                $query->where('type', 'like', "%$search%");
            });
        }

        if ($type) {
            $row->where('type', $type);
        }

        if ($brand_id) {
            $row->whereHas('brands', function ($query) use ($brand_id) {
                $query->whereIn('brands.id', $brand_id);
            });
        }


        $rows = $row->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Master Point'
        ]);
    }


    public function getDetailMasterPoint($master_point_id)
    {
        $row = MasterPoint::with('brands')->find($master_point_id);

        return response()->json([
            'status' => 'success',
            'data' => $row,
            'message' => 'Detail Master Point'
        ]);
    }

    public function saveMasterPoint(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'type'  => $request->type,
                'point'  => $request->point,
                'min_trans'  => $request->min_trans,
                'max_trans'  => $request->max_trans,
                'nominal'  => $request->nominal,
                'product_id' => $request->product_id,
                'product_sku' => $request->product_sku,
                'product_uom' => $request->product_uom
            ];

            $point = MasterPoint::create($data);
            $brand_id = json_decode($request->brand_id, true);
            $point->brands()->attach($brand_id);

            $dataLog = [
                'log_type' => '[fis-dev]master_point',
                'log_description' => 'Create Master Point - ' . $point->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Poin Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Poin Gagal Disimpan'
            ], 400);
        }
    }

    public function updateMasterPoint(Request $request, $master_point_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'type'  => $request->type,
                'point'  => $request->point,
                'min_trans'  => $request->min_trans,
                'max_trans'  => $request->max_trans,
                'nominal'  => $request->nominal,
                'product_id' => $request->product_id,
                'product_sku' => $request->product_sku,
                'product_uom' => $request->product_uom
            ];
            $row = MasterPoint::find($master_point_id);

            $row->update($data);
            $brand_id = json_decode($request->brand_id, true);
            $row->brands()->sync($brand_id);

            $dataLog = [
                'log_type' => '[fis-dev]master_point',
                'log_description' => 'Update Master Point - ' . $master_point_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Poin Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Poin Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterPoint($master_point_id)
    {
        $row = MasterPoint::find($master_point_id);
        $row->delete();
        $row->brands()->detach();

        $dataLog = [
            'log_type' => '[fis-dev]master_point',
            'log_description' => 'Delete Master Point - ' . $master_point_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Data Master Poin berhasil dihapus'
        ]);
    }
}
