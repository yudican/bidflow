<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\GpSiteId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterSiteIDController extends Controller
{
    public function index($master_site_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterSiteId(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $master_siteId =  GpSiteId::query();
        if ($search) {
            $master_siteId->where(function ($query) use ($search) {
                $query->where('site_id', 'like', "%$search%");
            });
        }

        if ($status) {
            $master_siteId->whereIn('status', $status);
        }


        $master_siteIds = $master_siteId->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $master_siteIds,
            'message' => 'List Master Site ID'
        ]);
    }


    public function getDetailMasterSiteId($master_site_id)
    {
        $brand = GpSiteId::find($master_site_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Master Site ID'
        ]);
    }

    public function saveMasterSiteId(Request $request)
    {
        $siteId = GpSiteId::where('site_id', $request->site_id)->first();
        if ($siteId) {
            return response()->json([
                'status' => 'success',
                'message' => 'Maaf, Site Id yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $data = [
                'site_id'  => $request->site_id,
                'status'  => 1
            ];

            $master = GpSiteId::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]gp_site_id',
                'log_description' => 'Create Master Site ID - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Site ID Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Site ID Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateMasterSiteId(Request $request, $master_site_id)
    {
        $siteId = GpSiteId::where('site_id', $request->site_id)->where('id', '!=', $master_site_id)->first();
        if ($siteId) {
            return response()->json([
                'status' => 'success',
                'message' => 'Maaf, Site Id yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $data = [
                'site_id'  => $request->site_id,
            ];
            $row = GpSiteId::find($master_site_id);
            $data['status'] = $row->status;
            if ($request->status == 1) {
                $data['status'] = 1;
            }
            if ($request->status === 0) {
                $data['status'] = 0;
            }

            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]gp_site_id',
                'log_description' => 'Update Master Site ID - ' . $master_site_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Site ID Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Site ID Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterSiteId($master_site_id)
    {
        $banner = GpSiteId::find($master_site_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]gp_site_id',
            'log_description' => 'Delete Master Site Id - ' . $master_site_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Master Site ID berhasil dihapus'
        ]);
    }
}
