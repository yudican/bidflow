<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\MasterPph;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterPphController extends Controller
{
    public function index($master_pph_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterPph(Request $request)
    {
        $search = $request->search;

        $master_tax =  MasterPph::query();
        if ($search) {
            $master_tax->where(function ($query) use ($search) {
                $query->where('pph_title', 'like', "%$search%");
            });
        }


        $master_taxs = $master_tax->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $master_taxs,
            'message' => 'List Master PPH'
        ]);
    }


    public function getDetailMasterPph($master_pph_id)
    {
        $brand = MasterPph::find($master_pph_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Master PPH'
        ]);
    }

    public function saveMasterPph(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'pph_title'  => $request->pph_title,
                'pph_percentage'  => $request->pph_percentage,
                'pph_amount'  => $request->pph_amount,
            ];

            $master = MasterPph::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_pph',
                'log_description' => 'Create Master Pph - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master PPH Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master PPH Gagal Disimpan'
            ], 400);
        }
    }

    public function updateMasterPph(Request $request, $master_pph_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'pph_title'  => $request->pph_title,
                'pph_percentage'  => $request->pph_percentage,
                'pph_amount'  => $request->pph_amount,
            ];
            $row = MasterPph::find($master_pph_id);
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_pph',
                'log_description' => 'Update Master Pph - ' . $master_pph_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master PPH Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master PPH Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterPph($master_pph_id)
    {
        $banner = MasterPph::find($master_pph_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_pph',
            'log_description' => 'Delete Master Pph - ' . $master_pph_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Master PPH berhasil dihapus'
        ]);
    }
}
