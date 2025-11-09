<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\SalesChannel;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesChannelController extends Controller
{
  public function index($sales_channel_id = null)
  {
    return view('spa.spa-index');
  }

  public function listSalesChannel(Request $request)
  {
    $search = $request->search;
    $warehouse_id = $request->warehouse_id;

    $sales_channel =  SalesChannel::query();
    if ($search) {
      $sales_channel->where(function ($query) use ($search) {
        $query->where('channel_name', 'like', "%$search%");
      });
    }

    if ($warehouse_id) {
      $sales_channel->whereIn('warehouse_id', $warehouse_id);
    }


    $sales_channels = $sales_channel->orderBy('created_at', 'desc')->paginate($request->perpage);
    return response()->json([
      'status' => 'success',
      'data' => $sales_channels,
      'message' => 'List Sales Channel'
    ]);
  }


  public function getDetailSalesChannel($sales_channel_id)
  {
    $brand = SalesChannel::find($sales_channel_id);

    return response()->json([
      'status' => 'success',
      'data' => $brand,
      'message' => 'Detail Sales Channel'
    ]);
  }

  public function saveSalesChannel(Request $request)
  {
    try {
      DB::beginTransaction();

      $data = [
        'channel_uid'  => hash('crc32', Carbon::now()->format('U')),
        'channel_name'  => $request->channel_name,
        'warehouse_id'  => $request->warehouse_id,

      ];

      SalesChannel::create($data);

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Channel Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Channel Gagal Disimpan'
      ], 400);
    }
  }

  public function updateSalesChannel(Request $request, $sales_channel_id)
  {
    try {
      DB::beginTransaction();
      $data = [
        'channel_uid'  => $request->channel_uid,
        'channel_name'  => $request->channel_name,
        'warehouse_id'  => $request->warehouse_id,
      ];
      $row = SalesChannel::find($sales_channel_id);
      $row->update($data);

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Channel Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Channel Gagal Disimpan'
      ], 400);
    }
  }

  public function deleteSalesChannel($sales_channel_id)
  {
    $banner = SalesChannel::find($sales_channel_id);
    $banner->delete();
    return response()->json([
      'status' => 'success',
      'message' => 'Data Sales Channel berhasil dihapus'
    ]);
  }
}
