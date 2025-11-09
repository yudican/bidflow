<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Str;

class WarehouseController extends Controller
{
    public function index($warehouse_id = null)
    {
        return view('spa.spa-index');
    }

    public function listWarehouse(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $warehouse =  Warehouse::query();
        if ($search) {
            $warehouse->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
                // $query->orWhere('location', 'like', "%$search%");
                // $query->orWhere('address', 'like', "%$search%");
            });
        }

        if ($status) {
            $warehouse->whereIn('status', $status);
        }


        $warehouses = $warehouse->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $warehouses,
            'message' => 'List Warehouse'
        ]);
    }


    public function getDetailWarehouse($warehouse_id)
    {
        $brand = Warehouse::with('users')->find($warehouse_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Warehouse'
        ]);
    }

    public function saveWarehouse(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'wh_id'  => $request->wh_id,
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

            $warehouse = Warehouse::create($data);
            $warehouse->users()->attach(json_decode($request->contacts, true), ['status' => 1]);

            $dataLog = [
                'log_type' => '[fis-dev]master_warehouse',
                'log_description' => 'Create Master Warehouse - ' . $warehouse->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            // Trigger orca
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->send('POST', 'https://brcd-testing.flimty.co/api/fis/trigger/warehouses', [
                'body' => '{}', // Kirim JSON kosong sebagai string
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Warehouse Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Warehouse Gagal Disimpan'
            ], 400);
        }
    }

    public function updateWarehouse(Request $request, $warehouse_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'wh_id'  => $request->wh_id,
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

            $row = Warehouse::find($warehouse_id);
            $row->update($data);
            $row->users()->sync(json_decode($request->contacts, true), ['status' => 1]);

            $dataLog = [
                'log_type' => '[fis-dev]master_warehouse',
                'log_description' => 'Update Master Warehouse - ' . $warehouse_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Warehouse Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Warehouse Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteWarehouse($warehouse_id)
    {
        $banner = Warehouse::find($warehouse_id);
        $banner->users()->detach();
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_warehouse',
            'log_description' => 'Delete Master Warehouse - ' . $warehouse_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Warehouse berhasil dihapus'
        ]);
    }


    // get warehouse list
    public function getWarehouseList()
    {
        $warehouse = DB::table('warehouses')
            ->select('id', 'name', 'slug')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $warehouse,
            'message' => 'List Warehouse'
        ]);
    }
}
