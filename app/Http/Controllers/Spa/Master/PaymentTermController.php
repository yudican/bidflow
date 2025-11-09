<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\LogError;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Str;

class PaymentTermController extends Controller
{
    public function index($payment_term_id = null)
    {
        return view('spa.spa-index');
    }

    public function listPaymentTerm(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $variant =  PaymentTerm::query();
        if ($search) {
            $variant->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $variant->whereIn('status', $status);
        }

        $variants = $variant->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $variants,
            'message' => 'List PaymentTerm'
        ]);
    }

    public function syncGpData()
    {
        $client = new Client();
        $company = CompanyAccount::find(auth()->user()->company_id || 1, ['account_code']);
        try {
            $response = $client->request('GET', getSetting('GP_URL') . '/MasterData/GetPaymentTermAll', [
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
                        'name'  => $value['name'],
                        'days_of'  => $value['days_of'],
                        'description'  => $value['description'],
                        'status_gp'  => 1,
                    ];

                    PaymentTerm::updateOrCreate(['days_of'  => $value['days_of']], $data);
                }
            }
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Payment Term GP',
            ]);
        }
    }

    public function getDetailPaymentTerm($payment_term_id)
    {
        $brand = PaymentTerm::find($payment_term_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail PaymentTerm'
        ]);
    }

    public function savePaymentTerm(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'description'  => $request->description,
                'days_of'  => $request->days_of
            ];

            $master = PaymentTerm::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_payment_term',
                'log_description' => 'Create Master Payment Term - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Payment Term berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data PaymentTerm Gagal Disimpan'
            ], 400);
        }
    }

    public function updatePaymentTerm(Request $request, $payment_term_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'description'  => $request->description,
                'days_of'  => $request->days_of
            ];
            $row = PaymentTerm::find($payment_term_id);
            $row->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]master_payment_term',
                'log_description' => 'Update Master Payment Term - ' . $payment_term_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Payment Term berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data PaymentTerm Gagal Disimpan'
            ], 400);
        }
    }

    public function deletePaymentTerm($payment_term_id)
    {
        $banner = PaymentTerm::find($payment_term_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_payment_term',
            'log_description' => 'Delete Master Payment Term - ' . $payment_term_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Data PaymentTerm berhasil dihapus'
        ]);
    }
}
