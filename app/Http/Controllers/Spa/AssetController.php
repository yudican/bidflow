<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Exports\PurchaseOrderExport;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\ProductNeed;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Asset;
use App\Models\AssetControlLog;
use App\Models\User;
use App\Models\Brand;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\BarcodeGeneratorHTML;

class AssetController extends Controller
{
    public function index($asset_id = null)
    {
        return view('spa.spa-index');
    }

    public function listAsset(Request $request)
    {
        $search = $request->search;
        $generate_date = $request->generate_date;
        $asset_name = $request->asset_name;
        $account_id = $request->account_id;
        $order =  Asset::query();

        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('item_name', 'like', "%$search%");
                $query->orWhere('barcode', 'like', "%$search%");
                $query->orWhere('asset_number', 'like', "%$search%");
                $query->orWhere('allocation_status', 'like', "%$search%");
            });
        }

        if ($asset_name) {
            $order->where('item_name', 'like', "%$asset_name%");
        }

        if ($generate_date) {
            $startDate = \Carbon\Carbon::createFromFormat('d-m-Y', $generate_date[0])->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('d-m-Y', $generate_date[1])->addDay()->format('Y-m-d');

            $order->whereBetween('generate_date', [$startDate, $endDate]);
        }

        $orders = $order->orderBy('created_at', 'desc')->orderBy('id', 'asc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'List Purchase Order'
        ]);
    }

    public function detailAsset($id)
    {
        $asset = Asset::with(['logs'])->find($id);

        return response()->json([
            'status' => 'success',
            'data' => $asset,
            'message' => 'Detail Asset'
        ]);
    }

    public function updateAsset(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $data = [
                'exp_date' => $request->exp_date,
                'notes' => $request->notes,
                'owner' => $request->owner,
                'warranty' => $request->warranty,
                'useful_life' => $request->useful_life,
                'asset_location' => $request->asset_location,
                'receiver_address' => $request->receiver_address,
                'allocation_status' => $request->allocation_status,
                'asset_number' => $request->asset_number
            ];

            $row = Asset::find($id);

            $row->update($data);
            $user = User::find($request->owner);

            // $dataLog = [
            //     'log_type' => '[fis-dev]asset-control',
            //     'log_description' => 'Update Asset Control - ' . $id,
            //     'log_user' => $request->owner,
            // ];
            // CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            AssetControlLog::create([
                'asset_id' => $id,
                'action' => 'Update Asset Control ke ' . $user?->name,
                'executed_by' => auth()->user()->id,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Asset Control Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Asset Control Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function print($id = null)
    {
        $asset = Asset::find($id);
        $brand = Brand::find($asset->brand_id);

        if (!empty($brand)) {
            $logo = getImage($brand->logo);
        } else {
            $logo = '';
        }
        $generator = new BarcodeGeneratorHTML();
        return view('print.barcode', ['data' =>  $asset, 'generator' => $generator, 'brand' => $brand, 'logo' => $logo]);
    }


    public function bulkPrint(Request $request)
    {
        $ids = $request->query('ids');

        if (empty($ids)) {
            return response()->json(['message' => 'No asset IDs provided.'], 400);
        }

        $idArray = explode(',', $ids);
        $assets = Asset::whereIn('id', $idArray)->get();
        if (!empty($assets)) {
            foreach ($assets as $ass) {
                $brand = Brand::find($ass->brand_id);
                $ass['brand_name'] = $brand->name;
                if (!empty($brand)) {
                    $ass['logo'] = getImage($brand->logo);
                } else {
                    $ass['logo'] = '';
                }
                $ass['pt_name'] = $brand->pt_name;
            }
        }

        if ($assets->isEmpty()) {
            return response()->json(['message' => 'No assets found for the provided IDs.'], 404);
        }

        $generator = new BarcodeGeneratorHTML();

        return view('print.bulk-barcode', ['assets' => $assets, 'generator' => $generator]);
    }
}
