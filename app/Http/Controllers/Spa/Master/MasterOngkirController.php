<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\MasterOngkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterOngkirController extends Controller
{
  public function index($master_ongkir_id = null)
  {
    return view('spa.spa-index');
  }

  public function listMasterOngkir(Request $request)
  {
    $search = $request->search;
    $status = $request->status_ongkir;

    $variant =  MasterOngkir::query();
    if ($search) {
      $variant->where(function ($query) use ($search) {
        $query->where('nama_ogkir', 'like', "%$search%");
        $query->orWhere('kode_ongkir', 'like', "%$search%");
        $query->orWhere('harga_ongkir', 'like', "%$search%");
        $query->orWhere('status_ongkir', 'like', "%$search%");
      });
    }

    if ($status) {
      $variant->whereIn('status_ongkir', $status);
    }

    // logistic_id
    if ($request->logistic_id) {
      $variant->whereHas('logistic', function ($query) use ($request) {
        $query->where('logistics.id', $request->logistic_id);
      });
    }



    $variants = $variant->orderBy('created_at', 'desc')->paginate($request->perpage);
    return response()->json([
      'status' => 'success',
      'data' => $variants,
      'message' => 'List Variant'
    ]);
  }


  public function getDetailMasterOngkir($master_ongkir_id)
  {
    $brand = MasterOngkir::find($master_ongkir_id);

    return response()->json([
      'status' => 'success',
      'data' => $brand,
      'message' => 'Detail Variant'
    ]);
  }

  public function saveMasterOngkir(Request $request)
  {
    try {
      DB::beginTransaction();
      $data = [
        'nama_ogkir'  => $request->nama_ogkir,
        'kode_ongkir'  => $request->kode_ongkir,
        'harga_ongkir'  => $request->harga_ongkir,
        'status_ongkir'  => 1,
        'start_date'  => $request->start_date,
        'end_date'  => $request->end_date,
      ];

      $ongkir = MasterOngkir::create($data);
      $ongkir->logistic()->attach($request->logistic_id);

      $dataLog = [
        'log_type' => '[fis-dev]master_ongkir',
        'log_description' => 'Create Master Ongkir - ' . $ongkir->id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Master Ongkir Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Master Ongkir Gagal Disimpan',
        'error' => $th->getMessage()
      ], 400);
    }
  }

  public function updateMasterOngkir(Request $request, $master_ongkir_id)
  {
    try {
      DB::beginTransaction();
      $data = [
        'nama_ogkir'  => $request->nama_ogkir,
        'kode_ongkir'  => $request->kode_ongkir,
        'harga_ongkir'  => $request->harga_ongkir,
        'start_date'  => $request->start_date,
        'status_ongkir'  => $request->status_ongkir,
        'end_date'  => $request->end_date,
      ];
      $row = MasterOngkir::find($master_ongkir_id);
      $row->update($data);
      $row->logistic()->sync($request->logistic_id);
      $dataLog = [
        'log_type' => '[fis-dev]master_ongkir',
        'log_description' => 'Update Master Ongkir - ' . $master_ongkir_id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Master Ongkir Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Master Ongkir Gagal Disimpan'
      ], 400);
    }
  }

  public function deleteMasterOngkir($master_ongkir_id)
  {
    $banner = MasterOngkir::find($master_ongkir_id);
    $banner->delete();
    $banner->logistic()->detach();
    $dataLog = [
      'log_type' => '[fis-dev]master_ongkir',
      'log_description' => 'Delete Master Ongkir - ' . $master_ongkir_id,
      'log_user' => auth()->user()->name,
    ];
    CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

    return response()->json([
      'status' => 'success',
      'message' => 'Data Master Ongkir berhasil dihapus'
    ]);
  }
}
