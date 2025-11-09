<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\LogError;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Str;

class PackageController extends Controller
{
  public function index($package_id = null)
  {
    return view('spa.spa-index');
  }

  public function listPackage(Request $request)
  {
    $search = $request->search;
    $status = $request->status;

    $banner =  Package::query();
    if ($search) {
      $banner->where(function ($query) use ($search) {
        $query->where('name', 'like', "%$search%");
      });
    }

    // if ($status >= 0) {
    //   $banner->whereIn('status', $status);
    // }


    $banners = $banner->orderBy('created_at', 'desc')->paginate($request->perpage ?? 10);
    return response()->json([
      'status' => 'success',
      'data' => $banners,
      'message' => 'List Package'
    ]);
  }

  public function syncGpData()
  {
    $client = new Client();
    $company = CompanyAccount::find(auth()->user()->company_id || 1, ['account_code']);
    try {
      $response = $client->request('GET', getSetting('GP_URL') . '/MasterData/GetUOFM', [
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . getSetting('GP_TOKEN_' . $company->account_code),
        ],
      ]);

      $responseJSON = json_decode($response->getBody(), true);
      setSetting('RESULT_SYNC', json_encode($responseJSON));
      if (in_array($responseJSON['code'], [200, 201])) {
        foreach ($responseJSON['data'] as $key => $value) {
          $data = [
            'name'  => $value['uofmid'],
            'status_gp'  => 1,
            'created_by' => 'GP'
          ];

          Package::updateOrCreate(['name'  => $value['uofmid']], $data);
        }
      }
    } catch (\Throwable $th) {
      LogError::updateOrCreate(['id' => 1], [
        'message' => $th->getMessage(),
        'trace' => $th->getTraceAsString(),
        'action' => 'Get UOM GP',
      ]);
    }
  }

  public function getDetailPackage($package_id)
  {
    $brand = Package::find($package_id);

    return response()->json([
      'status' => 'success',
      'data' => $brand,
      'message' => 'Detail Package'
    ]);
  }

  public function savePackage(Request $request)
  {
    try {
      DB::beginTransaction();
      $data = [
        'name'  => $request->name,
        'slug'  => Str::slug($request->name),
        'description'  => $request->description,
        'status'  => $request->status
      ];

      $pck = Package::create($data);

      $dataLog = [
        'log_type' => '[fis-dev]master_package',
        'log_description' => 'Create Master Package - ' . $pck->id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Package Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Package Gagal Disimpan'
      ], 400);
    }
  }

  public function updatePackage(Request $request, $package_id)
  {
    try {
      DB::beginTransaction();
      $data = [
        'name'  => $request->name,
        'slug'  => Str::slug($request->name),
        'description'  => $request->description,
        'status'  => $request->status
      ];
      $row = Package::find($package_id);
      $row->update($data);

      $dataLog = [
        'log_type' => '[fis-dev]master_package',
        'log_description' => 'Update Master Package - ' . $package_id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Package Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Package Gagal Disimpan'
      ], 400);
    }
  }

  public function deletePackage($package_id)
  {
    $banner = Package::find($package_id);
    $banner->delete();

    $dataLog = [
      'log_type' => '[fis-dev]master_package',
      'log_description' => 'Delete Master Package - ' . $package_id,
      'log_user' => auth()->user()->name,
    ];
    CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

    return response()->json([
      'status' => 'success',
      'message' => 'Data Package berhasil dihapus'
    ]);
  }
}
