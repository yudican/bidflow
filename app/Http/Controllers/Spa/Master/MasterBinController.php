<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\MasterBin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class MasterBinController extends Controller
{
    public function index($master_bin_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterBin(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $master_bin =  MasterBin::query();
        if ($search) {
            $master_bin->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
                $query->orWhere('location', 'like', "%$search%");
                $query->orWhere('address', 'like', "%$search%");
            });
        }

        if ($status) {
            $master_bin->whereIn('status', $status);
        }


        $master_bins = $master_bin->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $master_bins,
            'message' => 'List Master BIN'
        ]);
    }


    public function getDetailMasterBin($master_bin_id)
    {
        $brand = MasterBin::with('users')->find($master_bin_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Master BIN'
        ]);
    }

    public function saveMasterBin(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'slug'  => Str::slug($request->name),
                'location'  => $request->location,
                'address'  => $request->address,
                'status'  => $request->status,
                'telepon'  => formatPhone($request->telepon),
                'provinsi_id'  => $request->provinsi_id,
                'kabupaten_id'  => $request->kabupaten_id,
                'kecamatan_id'  => $request->kecamatan_id,
                'kelurahan_id'  => $request->kelurahan_id,
                'kodepos'  => $request->kodepos,
                'created_by'  => auth()->user()->id,
            ];

            $master_bin = MasterBin::create($data);
            $master_bin->users()->attach(json_decode($request->contacts, true), ['status' => 1]);

            $dataLog = [
                'log_type' => '[fis-dev]master_bin',
                'log_description' => 'Create Master Bin - ' . $master_bin->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master BIN Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master BIN Gagal Disimpan'
            ], 400);
        }
    }

    public function updateMasterBin(Request $request, $master_bin_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'slug'  => Str::slug($request->name),
                'location'  => $request->location,
                'address'  => $request->address,
                'status'  => $request->status,
                'telepon'  => formatPhone($request->telepon),
                'provinsi_id'  => $request->provinsi_id,
                'kabupaten_id'  => $request->kabupaten_id,
                'kecamatan_id'  => $request->kecamatan_id,
                'kelurahan_id'  => $request->kelurahan_id,
                'kodepos'  => $request->kodepos,
            ];

            $row = MasterBin::find($master_bin_id);
            $row->update($data);
            $row->users()->sync(json_decode($request->contacts, true), ['status' => 1]);

            $dataLog = [
                'log_type' => '[fis-dev]master_bin',
                'log_description' => 'Update Master Bin - ' . $master_bin_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master BIN Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master BIN Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterBin($master_bin_id)
    {
        $banner = MasterBin::find($master_bin_id);
        $banner->users()->detach();
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_bin',
            'log_description' => 'Delete Master Bin - ' . $master_bin_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Master BIN berhasil dihapus'
        ]);
    }
}
