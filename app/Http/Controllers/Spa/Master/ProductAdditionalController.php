<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\ProductAdditional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAdditionalController extends Controller
{
  public function index($product_additional_id = null)
  {
    return view('spa.spa-index');
  }

  public function listProductAdditional(Request $request)
  {
    $search = $request->search;
    $status = $request->status;

    $product =  ProductAdditional::query()->where('type', $request->type);
    if ($search) {
      $product->where(function ($query) use ($search) {
        $query->where('name', 'like', "%$search%");
        $query->orwhere('sku', 'like', "%$search%");
      });
    }

    if ($status) {
      $product->where('status', $status == 10 ? 0 : $status);
    }


    $products = $product->orderBy('created_at', 'desc')->paginate($request->perpage);
    return response()->json([
      'status' => 'success',
      'data' => $products,
      'message' => 'List product'
    ]);
  }

  public function getDetailProductAdditional($product_additional_id)
  {
    $brand = ProductAdditional::find($product_additional_id);

    return response()->json([
      'status' => 'success',
      'data' => $brand,
      'message' => 'Detail Variant'
    ]);
  }

  public function saveProductAdditional(Request $request)
  {
    $sku = ProductAdditional::whereSku($request->sku)->whereType($request->type)->first();
    if ($sku) {
      return response()->json([
        'status' => 'success',
        'message' => 'Maaf, SKU yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
      ], 400);
    }
    try {
      DB::beginTransaction();
      $data = [
        'name'  => $request->name,
        'sku'  => $request->sku,
        'status'  => $request->status,
        'notes'  => $request->notes,
        'type'  => $request->type,
      ];

      $master = ProductAdditional::create($data);

      $dataLog = [
        'log_type' => '[fis-dev]pengemasan',
        'log_description' => 'Create Pengemasan - ' . $master->id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Product Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Product Gagal Disimpan'
      ], 400);
    }
  }

  public function updateProductAdditional(Request $request, $product_additional_id)
  {
    $sku = ProductAdditional::whereSku($request->sku)->whereType($request->type)->where('id', '!=', $product_additional_id)->first();
    if ($sku) {
      return response()->json([
        'status' => 'success',
        'message' => 'Maaf, SKU yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
      ], 400);
    }

    try {
      DB::beginTransaction();
      $data = [
        'name'  => $request->name,
        'sku'  => $request->sku,
        'status'  => $request->status,
        'notes'  => $request->notes,
        'type'  => $request->type,
      ];
      $row = ProductAdditional::find($product_additional_id);
      $row->update($data);

      $dataLog = [
        'log_type' => '[fis-dev]pengemasan',
        'log_description' => 'Update Pengemasan - ' . $product_additional_id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Product Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Product Gagal Disimpan'
      ], 400);
    }
  }

  public function deleteProductAdditional($product_additional_id)
  {
    $banner = ProductAdditional::find($product_additional_id);
    $banner->delete();

    $dataLog = [
      'log_type' => '[fis-dev]pengemasan',
      'log_description' => 'Delete Pengemasan - ' . $product_additional_id,
      'log_user' => auth()->user()->name,
    ];
    CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

    return response()->json([
      'status' => 'success',
      'message' => 'Data Product berhasil dihapus'
    ]);
  }
}
