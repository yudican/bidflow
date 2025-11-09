<?php

namespace App\Http\Controllers\Spa\ProductManagement;

use App\Exports\ProductSkuConvertExport;
use App\Exports\ProductSkuExport;
use App\Exports\VendorExport;
use App\Exports\ReceivingExport;
use App\Http\Controllers\Controller;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ConvertController extends Controller
{
    public function index($convert_id = null)
    {
        return view('spa.spa-index');
    }

    public function listConvert(Request $request)
    {
        $user = auth()->user();
        $search = $request->search;
        $convert = ProductConvert::query();

        if ($user->role->role_type != 'superadmin') {
            $convert->where('user_id', $user->id);
        }

        if ($search) {
            $convert->where(function ($query) use ($search) {
                $query->where('convert_date', 'like', "%$search%");
                $query->orWhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            });
        }

        $rows = $convert->orderBy('convert_date', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Convert'
        ]);
    }

    public function listConvertDetail(Request $request, $convert_id)
    {
        $user = auth()->user();
        $search = $request->search;
        $convert = ProductConvertDetail::query()->where('product_convert_id', $convert_id);

        if ($user->role->role_type != 'superadmin') {
            $convert->where('user_id', $user->id);
        }

        if ($search) {
            $convert->where(function ($query) use ($search) {
                $query->where('produk_nama', 'like', "%$search%");
                $query->orWhere('trx_id', 'like', "%$search%");
                $query->orWhere('user', 'like', "%$search%");
                $query->orWhere('channel', 'like', "%$search%");
                $query->orWhere('sku', 'like', "%$search%");
                $query->orWhere('resi', 'like', "%$search%");
            });
        }

        $rows = $convert->orderBy('tanggal_transaksi', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Convert'
        ]);
    }

    public function export($convert_id)
    {
        $product_convert = ProductConvert::find($convert_id);
        $file_name = 'convert/data-product-convert-' . $product_convert->convert_date . '.xlsx';
        Excel::store(new ProductSkuExport($product_convert), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
    public function exportConvert($convert_id)
    {
        $product_convert = ProductConvert::find($convert_id);
        $file_name = 'convert/data-product-convert-' . $product_convert->convert_date . '.xlsx';
        Excel::store(new ProductSkuConvertExport($product_convert), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function exportVendor()
    {
        $purchase_vendor = PurchaseOrder::groupBy('vendor_name')->get();

        // foreach($purchase_vendor as $pv){
        //     $nominalVendor = 

        // }

        $file_name = 'convert/data-vendor-' . $purchase_vendor->convert_date . '.xlsx';
        Excel::store(new VendorExport($purchase_vendor), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function exportPoReceiving()
    {
        $purchase_vendor = PurchaseOrder::groupBy('vendor_name')->get();
        $file_name = 'convert/data-po-receiving.xlsx';
        Excel::store(new ReceivingExport($purchase_vendor), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
}
