<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\GpCheckbook;
use App\Models\LogError;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckbookController extends Controller
{
  public function index($checkbook_id = null)
  {
    return view('spa.spa-index');
  }

  public function listCheckbook(Request $request)
  {
    $search = $request->search;
    $status = $request->status;

    $master_tax =  GpCheckbook::query();
    if ($search) {
      $master_tax->where(function ($query) use ($search) {
        $query->where('bank_name', 'like', "%$search%");
        $query->orWhere('description', 'like', "%$search%");
        $query->orWhere('company_address', 'like', "%$search%");
        $query->orWhere('bank_account', 'like', "%$search%");
        $query->orWhere('currency_id', 'like', "%$search%");
        $query->orWhere('status', 'like', "%$search%");
      });
    }

    if ($status) {
      $master_tax->whereIn('status', $status);
    }


    $master_taxs = $master_tax->orderBy('created_at', 'desc')->paginate($request->perpage);
    return response()->json([
      'status' => 'success',
      'data' => $master_taxs,
      'message' => 'List MasterTax'
    ]);
  }

  public function syncGpData()
  {
    $client = new Client();
    $company = CompanyAccount::find(auth()->user()->company_id || 1, ['account_code']);
    try {
      $response = $client->request('GET', getSetting('GP_URL') . '/MasterData/GetCheckbookAll', [
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . getSetting('GP_TOKEN_' . $company->account_code),
        ],
      ]);

      $responseJSON = json_decode($response->getBody(), true);
      if (in_array($responseJSON['code'], [200, 201])) {
        foreach ($responseJSON['data'] as $key => $value) {
          $data = [
            'bank_name' => $value['checkbook_id'],
            'description' => $value['description'],
            'company_address' => $value['company_address'],
            'bank_account' => $value['bank_account'],
            'currency_id' => $value['currency_id'],
            'status' => $value['status'],
            'gp_status'  => 1,
          ];

          GpCheckbook::updateOrCreate(['bank_name'  => $value['checkbook_id']], $data);
        }
      }
    } catch (\Throwable $th) {
      LogError::updateOrCreate(['id' => 1], [
        'message' => $th->getMessage(),
        'trace' => $th->getTraceAsString(),
        'action' => 'Get Vendor GP',
      ]);
    }
  }


  public function getDetailCheckbook($checkbook_id)
  {
    $vendor = GpCheckbook::find($checkbook_id);

    return response()->json([
      'status' => 'success',
      'data' => $vendor,
      'message' => 'Detail Vendor'
    ]);
  }

  public function saveCheckbook(Request $request)
  {
    try {
      DB::beginTransaction();
      $data = [
        'bank_name' => $request->bank_name,
        'description' => $request->description,
        'company_address' => $request->company_address,
        'bank_account' => $request->bank_account,
        'currency_id' => $request->currency_id,
        'status' => $request->status,
      ];

      $master = GpCheckbook::create($data);
      $dataLog = [
        'log_type' => '[fis-dev]GpCheckbook',
        'log_description' => 'Create Master GpCheckbook - ' . $master->id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Checkbook Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data Checkbook Gagal Disimpan'
      ], 400);
    }
  }

  public function updateCheckbook(Request $request, $checkbook_id)
  {
    try {
      DB::beginTransaction();
      $data = [
        'bank_name' => $request->bank_name,
        'description' => $request->description,
        'company_address' => $request->company_address,
        'bank_account' => $request->bank_account,
        'currency_id' => $request->currency_id,
        'status' => $request->status,
      ];
      $row = GpCheckbook::find($checkbook_id);
      $row->update($data);

      $dataLog = [
        'log_type' => '[fis-dev]GpCheckbook',
        'log_description' => 'Update GpCheckbook - ' . $checkbook_id,
        'log_user' => auth()->user()->name,
      ];
      CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

      DB::commit();
      return response()->json([
        'status' => 'success',
        'message' => 'Data GpCheckbook Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollback();
      return response()->json([
        'status' => 'success',
        'message' => 'Data GpCheckbook Gagal Disimpan'
      ], 400);
    }
  }

  public function deleteCheckbook($checkbook_id)
  {
    $banner = GpCheckbook::find($checkbook_id);
    $banner->delete();
    $dataLog = [
      'log_type' => '[fis-dev]GpCheckbook',
      'log_description' => 'Delete GpCheckbook - ' . $checkbook_id,
      'log_user' => auth()->user()->name,
    ];
    CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
    return response()->json([
      'status' => 'success',
      'message' => 'Data GpCheckbook berhasil dihapus'
    ]);
  }
}
