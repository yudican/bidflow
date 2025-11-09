<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\LogError;
use App\Models\Logistic;
use App\Models\LogisticRate;
use App\Models\ShippingVoucher;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LogisticController extends Controller
{
    public function index($logistic_id = null)
    {
        return view('spa.spa-index');
    }

    public function listLogistic(Request $request)
    {
        $search = $request->search;
        $status = $request->logistic_status;

        $logistic =  Logistic::query();
        if ($search) {
            $logistic->where(function ($query) use ($search) {
                $query->where('logistic_name', 'like', "%$search%");
            });
        }

        if ($status) {
            $logistic->where('logistic_status', $status == 10 ? 0 : $status);
        }

        $logistics = $logistic->where('logistic_type', $request->logistic_type)->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $logistics,
            'message' => 'List Logistic'
        ]);
    }

    public function listLogisticRates(Request $request)
    {
        $search = $request->search;

        $logistic =  LogisticRate::where('logistic_id', $request->logistic_id);
        if ($search) {
            $logistic->where(function ($query) use ($search) {
                $query->where('logistic_rate_name', 'like', "%$search%");
                $query->orWhere('logistic_rate_code', 'like', "%$search%");
            });
        }


        $logistics = $logistic->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $logistics,
            'message' => 'List Logistic Rates'
        ]);
    }

    public function updateStatusLogistic(Request $request)
    {
        $rate = Logistic::find($request->logistic_id);

        if ($rate) {
            $rate->update([$request->field => $request->value]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status Gagal Diupdate'
        ], 400);
    }

    public function updateStatusLogisticRates(Request $request)
    {
        $rate = LogisticRate::find($request->logistic_rates_id);

        if ($rate) {
            $rate->update([$request->field => $request->value]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status Berhasil Diupdate'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status Gagal Diupdate'
        ], 400);
    }

    public function updateSyncLogistic()
    {
        $client = new Client();
        try {
            DB::beginTransaction();
            $response = $client->request('GET', getSetting('LOGISTIC_URL') . '/api/client/shipment/v1/logistics', [
                'headers' => [
                    'Authorization' => 'Bearer ' . getSetting('LOGISTIC_AUTH_TOKEN')
                ],
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                foreach ($responseJSON['data'] as $key => $item) {
                    $logistic = Logistic::updateOrCreate(['logistic_name' => $item['logistic_name']], [
                        'logistic_name' => $item['logistic_name'],
                        'logistic_url_logo' => $item['logistic_url_logo'],
                        'logistic_status' => $item['is_active'],
                        'logistic_original_id' => $item['id'],
                    ]);
                    foreach ($item['rates'] as $key => $rate) {
                        $logistic->logisticRates()->updateOrCreate(['logistic_rate_code' => $rate['rate_code']], [
                            'logistic_rate_code' => $rate['rate_code'],
                            'logistic_rate_name' => $rate['rate_name'],
                            'logistic_rate_status' => $rate['is_active'],
                            'logistic_cod_status' => $rate['is_support_cod'],
                            'logistic_rate_original_id' => $rate['id'],
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Sync Logistic Berhasil'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Update kuir popaket (updateKurir) logisticController',
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Update Data',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function getLogisticDiscount($logistic_rate_id)
    {
        $discount = ShippingVoucher::where('logistic_rate_id', $logistic_rate_id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $discount,
            'message' => 'Logistic Discount'
        ]);
    }

    public function saveLogisticDiscount(Request $request)
    {
        $discount = ShippingVoucher::updateOrCreate(['logistic_rate_id' => $request->logistic_rate_id], [
            'shipping_price_discount' => $request->shipping_price_discount,
            'shipping_price_discount_start' => $request->shipping_price_discount_start,
            'shipping_price_discount_end' => $request->shipping_price_discount_end,
            'shipping_price_sales_channel' => $request->shipping_price_sales_channel,
            'shipping_price_discount_status' => $request->shipping_price_discount_status,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $discount,
            'message' => 'Logistic Berhasil Disimpan'
        ]);
    }

    public function saveLogistic(Request $request, $logistic_id = null)
    {
        try {
            DB::beginTransaction();
            $data = [
                'logistic_name'  => $request->logistic_name,
                'logistic_type'  => $request->logistic_type,
            ];

            if ($request->image) {
                $image = $this->uploadImage($request, 'image');
                $data['logistic_url_logo'] = getImage($image);
            }

            $logis = Logistic::updateOrCreate(['id' => $logistic_id], $data);

            $dataLog = [
                'log_type' => '[fis-dev]master_logistic',
                'log_description' => 'Create Master Logistic - ' . $logis->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Berhasil Disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Gagal Disimpan'
            ], 400);
        }
    }

    public function saveLogisticRates(Request $request, $logistic_rates_id = null)
    {
        try {
            DB::beginTransaction();
            $data = [
                'logistic_id'  => $request->logistic_id,
                'logistic_rate_code'  => $request->logistic_rate_code,
                'logistic_rate_name'  => $request->logistic_rate_name,
            ];

            LogisticRate::updateOrCreate(['id' => $logistic_rates_id], $data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Rate Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Rate Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteLogistic($logistic_id)
    {
        try {
            DB::beginTransaction();
            $logistic = Logistic::find($logistic_id);
            $logistic->delete();

            $dataLog = [
                'log_type' => '[fis-dev]master_logistic',
                'log_description' => 'Delete Master Logistic - ' . $logistic_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Berhasil Dihapus'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Gagal Dihapus'
            ], 400);
        }
    }

    public function deleteLogisticRates($logistic_rates_id)
    {
        try {
            DB::beginTransaction();
            $logistic = LogisticRate::find($logistic_rates_id);
            $logistic->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Rate Berhasil Dihapus'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Logistic Rate Gagal Dihapus'
            ], 400);
        }
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }
        $file = Storage::disk('s3')->put('upload/master/logistic', $request[$path], 'public');
        return $file;
    }
}
