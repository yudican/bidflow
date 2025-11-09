<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\LogError;
use App\Models\MasterTax;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class MasterTaxController extends Controller
{
    public function index($master_tax_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMasterTax(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $master_tax =  MasterTax::query();
        if ($search) {
            $master_tax->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
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
            $response = $client->request('GET', getSetting('GP_URL') . '/MasterData/GetTaxAll', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('GP_TOKEN_' . $company?->account_code),
                ],
            ]);

            $responseJSON = json_decode($response->getBody(), true);

            if (in_array($responseJSON['code'], [200, 201])) {
                foreach ($responseJSON['data'] as $key => $value) {
                    $data = [
                        'tax_code'  => $value['tax_id_number'],
                        'tax_percentage'  => floor($value['tax_percentage']),
                        'gp_status'  => 1,
                    ];

                    MasterTax::updateOrCreate(['tax_code'  => $value['tax_id_number']], $data);
                }
            }
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Tax GP',
            ]);
        }
    }

    public function getDetailMasterTax($master_tax_id)
    {
        $brand = MasterTax::find($master_tax_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail MasterTax'
        ]);
    }

    public function saveMasterTax(Request $request)
    {
        $request->validate([
            'tax_code' => 'required|unique:master_tax,tax_code',
            'tax_percentage' => 'required|numeric',
        ], [
            'tax_code.unique' => 'Maaf, Kode Tax yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.',
        ]);

        try {
            DB::beginTransaction();
            $data = [
                'tax_code'  => $request->tax_code,
                'tax_percentage'  => $request->tax_percentage
            ];

            $master = MasterTax::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_tax',
                'log_description' => 'Create Master Tax - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data MasterTax Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data MasterTax Gagal Disimpan'
            ], 400);
        }
    }

    public function updateMasterTax(Request $request, $master_tax_id)
    {
        $request->validate([
            'tax_code' => 'required|unique:master_tax,tax_code,' . $master_tax_id,
            'tax_percentage' => 'required|numeric',
        ], [
            'tax_code.unique' => 'Maaf, Kode Tax yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.',
        ]);
        
        try {
            DB::beginTransaction();
            $data = [
                'tax_code'  => $request->tax_code,
                'tax_percentage'  => $request->tax_percentage
            ];
            $row = MasterTax::find($master_tax_id);
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_tax',
                'log_description' => 'Update Master Tax - ' . $master_tax_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data MasterTax Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data MasterTax Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMasterTax($master_tax_id)
    {
        $banner = MasterTax::find($master_tax_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_tax',
            'log_description' => 'Delete Master Tax - ' . $master_tax_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data MasterTax berhasil dihapus'
        ]);
    }
}
