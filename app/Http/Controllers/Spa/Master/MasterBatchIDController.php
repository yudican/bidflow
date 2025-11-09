<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\GpBatchId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterBatchIDController extends Controller
{
    public function index($master_batch_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterBatchId(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $masterBatchID =  GpBatchId::query();
        if ($search) {
            $masterBatchID->where(function ($query) use ($search) {
                $query->where('batch_code', 'like', "%$search%");
                $query->orWhere('frequency', 'like', "%$search%");
            });
        }

        if ($status) {
            $masterBatchID->whereIn('status', $status);
        }


        $masterBatchIDs = $masterBatchID->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $masterBatchIDs,
            'message' => 'List Master Batch ID'
        ]);
    }


    public function getDetailMasterBatchId($master_batch_id)
    {
        $brand = GpBatchId::find($master_batch_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Master Batch ID'
        ]);
    }

    public function saveMasterBatchId(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'batch_code'  => $request->batch_code,
                'origin'  => $request->origin,
                'status'  => $request->status,
                'frequency'  => $request->frequency,
            ];

            $master = GpBatchId::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]gp_batch_id',
                'log_description' => 'Create Master Batch ID - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Batch ID Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Batch ID Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateMasterBatchId(Request $request, $master_batch_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'batch_code'  => $request->batch_code,
                'origin'  => $request->origin,
                'status'  => $request->status,
                'frequency'  => $request->frequency,
            ];
            $row = GpBatchId::find($master_batch_id);
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]gp_batch_id',
                'log_description' => 'Update Master Batch ID - ' . $master_batch_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Batch ID Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Batch ID Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterBatchId($master_batch_id)
    {
        $banner = GpBatchId::find($master_batch_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]gp_batch_id',
            'log_description' => 'Delete Master Batch ID - ' . $master_batch_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Master Batch ID berhasil dihapus'
        ]);
    }
}
