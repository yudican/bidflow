<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\MasterDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class MasterDiscountController extends Controller
{
    public function index($master_discount_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterDiscount(Request $request)
    {

        $search = $request->search;
        $status = $request->status;
        $sales_channel = $request->sales_channel;
        $sales_tag = $request->sales_tag;

        $master_discount =  MasterDiscount::query();
        if ($search) {
            $master_discount->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%");
            });
        }

        if ($status) {
            $master_discount->whereIn('status', $status);
        }

        if ($sales_channel) {
            $master_discount->where('sales_channel', 'like', "%$sales_channel%");
        }

        if ($sales_tag) {
            $master_discount->where('sales_tag', $sales_tag);
        }


        $master_discounts = $master_discount->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $master_discounts,
            'message' => 'List Master Discount'
        ]);
    }


    public function getDetailMasterDiscount($master_discount_id)
    {
        $brand = MasterDiscount::find($master_discount_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Master Discount'
        ]);
    }

    public function saveMasterDiscount(Request $request)
    {
        try {
            DB::beginTransaction();
            $sales_channel = json_decode($request->sales_channel, true);
            $data = [
                'title'  => $request->title,
                'percentage'  => $request->percentage,
                'sales_tag'  => $request->sales_tag,
                'sales_channel'  => implode(',', $sales_channel),
            ];

            $master = MasterDiscount::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_discount',
                'log_description' => 'Create Master Discount - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Discount Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Discount Gagal Disimpan'
            ], 400);
        }
    }

    public function updateMasterDiscount(Request $request, $master_discount_id)
    {
        try {
            DB::beginTransaction();
            $sales_channel = json_decode($request->sales_channel, true);
            $data = [
                'title'  => $request->title,
                'percentage'  => $request->percentage,
                'sales_tag'  => $request->sales_tag,
                'sales_channel'  => implode(',', $sales_channel),
            ];

            $row = MasterDiscount::find($master_discount_id);
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_discount',
                'log_description' => 'Update Master Discount - ' . $master_discount_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Discount Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Master Discount Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterDiscount($master_discount_id)
    {
        $banner = MasterDiscount::find($master_discount_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_discount',
            'log_description' => 'Delete Master Discount - ' . $master_discount_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Data Master Discount berhasil dihapus'
        ]);
    }
}
