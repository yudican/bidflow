<?php

namespace App\Http\Controllers\Spa\ProductManagement;

use App\Http\Controllers\Controller;
use App\Imports\Product\ProductSKU;
use App\Jobs\ConvertHistory;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\ProductImportTemp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index($import_id = null)
    {
        return view('spa.spa-index');
    }

    public function listImport(Request $request)
    {
        $search = $request->search;
        $import = ProductImportTemp::query()->where('user_id', auth()->user()->id)->where('status_import', 0);

        if ($search) {
            $import->where(function ($query) use ($search) {
                $query->where('produk_nama', 'like', "%$search%");
                $query->orWhere('trx_id', 'like', "%$search%");
                $query->orWhere('user', 'like', "%$search%");
                $query->orWhere('channel', 'like', "%$search%");
                $query->orWhere('sku', 'like', "%$search%");
                $query->orWhere('resi', 'like', "%$search%");
            });
        }

        $rows = $import->orderBy('status_convert', 'asc')->paginate($request->perpage);
        $total_failed = $this->getCount('failed');
        $total_success = $this->getCount();

        $total = $total_failed + $total_success;
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'data_count' => [
                'success' => $this->getCount(),
                'failed' => $total_failed,
                'showImport' =>  $total < 1 ? true : false,
                'showConvert' => $total > 0 && $total_success > 0 && $total_failed < 1 ? true : false,
            ],
            'message' => 'List Import'
        ]);
    }

    public function saveImport(Request $request)
    {
        $user_id = auth()->user()->id;
        removeSetting('product_convert_count_' . $user_id);
        removeSetting('product_import_count_' . $user_id);
        removeSetting('product_import_success_' . $user_id);
        Excel::import(new ProductSKU, $request->file);
        $this->checkImportProgress();
        return response()->json([
            'status' => 'success',
            'message' => 'Import sedang diproses'
        ]);
    }

    public function saveConvert()
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $convert = ProductConvert::where('convert_user_id', $user->id)->whereStatus('failed')->first();
            if ($convert) {
                ProductConvertDetail::where('product_convert_id', $convert->id)->delete();
            }

            $product_import = ProductImportTemp::where('user_id', $user->id)->where('status_import', 0)->count();
            setSetting('product_convert_count_' . $user->id, $product_import);

            if (!$convert) {
                $convert = ProductConvert::create([
                    'convert_user_id' => $user->id,
                    'convert_date' => now(),
                ]);
            }

            setSetting('product_convert_id_' . $user->id, $convert->id);
            ConvertHistory::dispatch($convert->id, $user->id)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Convert sedang diproses'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Convert gagal diproses'
            ], 400);
        }
    }

    public function discardImport()
    {
        $user_id = auth()->user()->id;
        removeSetting('product_convert_count_' . $user_id);
        removeSetting('product_import_count_' . $user_id);
        removeSetting('product_import_success_' . $user_id);
        ProductImportTemp::where('user_id', $user_id)->where('status_import', 0)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Import dibatalkan'
        ]);
    }

    public function checkImportProgress()
    {
        $user_id = auth()->user()->id;
        $total = getSetting('product_import_count_' . $user_id);
        $success = getSetting('product_import_success_' . $user_id);
        return response()->json([
            'status' => 'success',
            'message' => 'Check Import Progress',
            'data' => [
                'total' => $total,
                'success' => $this->getCount(),
                'failed' => $this->getCount('failed'),
                'progress' => getPercentage($success, $total)
            ]
        ]);
    }

    public function getCount($status_convert = 'success')
    {
        $total = ProductImportTemp::where('status_import', 0)->where('status_convert', $status_convert)->where('user_id', auth()->user()->id)->count();

        return $total;
    }
}
