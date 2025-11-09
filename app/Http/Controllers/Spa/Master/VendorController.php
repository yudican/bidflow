<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\LogError;
use App\Models\Vendor;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Str;

class VendorController extends Controller
{
    public function index($master_tax_id = null)
    {
        return view('spa.spa-index');
    }

    public function listVendor(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $master_tax =  Vendor::query();
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
            $response = $client->request('GET', getSetting('GP_URL') . '/MasterData/GetVendorAll', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('GP_TOKEN_' . $company->account_code),
                ],
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            if (in_array($responseJSON['code'], [200, 201])) {
                foreach ($responseJSON['data'] as $key => $value) {
                    $data = [
                        'vendor_code' => $value['id'],
                        'name' => $value['name'],
                        'short_name' => $value['short_name'],
                        'primary_address' => $value['primary_address'],
                        'purchase_address' => $value['purchase_address'],
                        'ship_address' => $value['ship_address'],
                        'remit_address' => $value['ship_address1'],
                        'contact' => $value['contact'],
                        'address_1' => $value['address_1'],
                        'address_2' => $value['address_2'],
                        'address_3' => $value['address_3'],
                        'city' => $value['city'],
                        'state' => $value['state'],
                        'zip_code' => $value['zip_code'],
                        'country' => $value['country'],
                        'phone_1' => $value['phone_1'],
                        'phone_2' => $value['phone_2'],
                        'phone_3' => $value['phone_3'],
                        'fax' => $value['fax'],
                        'ups_zone' => $value['ups_zone'],
                        'shipping_method' => $value['shipping_method'],
                        'tax_schedule' => $value['tax_schedule'],
                        'tax_id' => $value['tax_id'],
                        'gp_status'  => 1,
                    ];

                    Vendor::updateOrCreate(['vendor_code'  => $value['id']], $data);
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


    public function getDetailVendor($master_tax_id)
    {
        $vendor = Vendor::find($master_tax_id);

        return response()->json([
            'status' => 'success',
            'data' => $vendor,
            'message' => 'Detail Vendor'
        ]);
    }

    public function saveVendor(Request $request)
    {
        $brand = Vendor::where('vendor_code', $request->vendor_code)->first();
        if ($brand) {
            return response()->json([
                'status' => 'success',
                'message' => 'Maaf, Vendor Code yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'short_name' => $request->short_name,
                'vendor_code' => $request->vendor_code,
                'primary_address' => $request->primary_address,
                'purchase_address' => $request->purchase_address,
                'ship_address' => $request->ship_address,
                'remit_address' => $request->remit_address,
                'contact' => $request->contact,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'address_3' => $request->address_3,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'country' => $request->country,
                'phone_1' => $request->phone_1,
                'phone_2' => $request->phone_2,
                'phone_3' => $request->phone_3,
                'fax' => $request->fax
            ];

            $master = Vendor::create($data);
            syncGpMaster('VENDOR_ID', $request->vendor_code, '/MasterData/SyncVendorFlag');
            $dataLog = [
                'log_type' => '[fis-dev]vendor',
                'log_description' => 'Create Master Vendor - ' . $master->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            // Trigger orca
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->send('POST', 'https://brcd-testing.flimty.co/api/fis/trigger/vendors', [
                'body' => '{}', // Kirim JSON kosong sebagai string
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Vendor Berhasil Disimpan',
                'api' => $response->json()
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Data Vendor Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateVendor(Request $request, $master_tax_id)
    {
        $siteId = Vendor::where('vendor_code', $request->vendor_code)->where('id', '!=', $master_tax_id)->first();
        if ($siteId) {
            return response()->json([
                'status' => 'success',
                'message' => 'Maaf, Kode Vendor yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'short_name' => $request->short_name,
                'primary_address' => $request->primary_address,
                'purchase_address' => $request->purchase_address,
                'ship_address' => $request->ship_address,
                'remit_address' => $request->remit_address,
                'contact' => $request->contact,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'address_3' => $request->address_3,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'country' => $request->country,
                'phone_1' => $request->phone_1,
                'phone_2' => $request->phone_2,
                'phone_3' => $request->phone_3,
                'fax' => $request->fax
            ];
            $row = Vendor::find($master_tax_id);
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

    public function deleteVendor($master_tax_id)
    {
        $banner = Vendor::find($master_tax_id);
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_tax',
            'log_description' => 'Delete Master Tax - ' . $master_tax_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Vendor berhasil dihapus'
        ]);
    }

    public function submitGP(Request $request)
    {
        // echo"<pre>";print_r($request->items);die();
        $contact = Vendor::whereIn('id', $request->items)->get();
        $header = [];
        $line = [];
        $data_submit = [];
        foreach ($contact as $key => $item) {
            if ($item->status_submit_gp == "submited") {
                return response()->json([
                    'message' => 'Data vendor sudah pernah di submit',
                    'status' => 'failed',
                ]);
            }

            $data_submit[$item->uid]['headers'][] =
                [
                    'ID' => $item->vendor_code,
                    'NAME' => $item->name,
                    'SHORT_NAME' => $item->short_name,
                    'CONTACT' => $item->contact,
                    'ADDRESS_1' => $item->address_1,
                    'ADDRESS_2' => $item->address_2,
                    'ADDRESS_3' => $item->address_3,
                    'CITY' => $item->city,
                    'STATE' => $item->state,
                    'ZIP_CODE' => $item->zip_code,
                    'COUNTRY' => $item->country,
                    'PHONE_1' => $item->phone_1,
                    'PHONE_2' => $item->phone_2,
                    'PHONE_3' => $item->phone_3,
                    'FAX' => $item->fax,
                    'UPS_ZONE' => $item->ups_zone,
                    'SHIPPING_METHOD' => $item->shipping_method,
                    'TAX_SCHEDULE' => $item->tax_schedule,
                    'STATUS' => 1
                ];
        }

        $submitLog = OrderSubmitLog::create([
            'submited_by' => auth()->user()->id,
            'type_si' => 'vendor',
            'vat' => '',
            'tax' => '',
        ]);

        $body = [];
        foreach ($data_submit as $key => $value) {
            $body[] = json_encode([
                'header' => $value['headers']
            ]);
        }

        $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
        foreach ($body as $key => $body_value) {
            if ($isUseQueue) {
                SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
            } else {
                $order_log_id = $submitLog->id;

                setSetting('GP_BODY_' . $order_log_id, $body_value);

                try {
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => getSetting('GP_URL') . '/Vendor/VendorEntry',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $body_value,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . getSetting('GP_TOKEN_2')
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $responseJSON = json_decode($response, true);
                    // check is string
                    if (!$responseJSON && is_string($response)) {
                        if ($response == "Vendor ID already exists") {
                            return response()->json([
                                'message' => 'Data vendor sudah ada di GP',
                                'status' => 'failed',
                            ]);
                        }

                        setSetting('GP_RESPONSE_ERROR_VENDOR3_' . $order_log_id, $response);
                        foreach ($contact as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $response
                            ]);
                        }

                        return;
                    }

                    // Check if any error occured
                    if (curl_errno($curl)) {
                        setSetting('GP_RESPONSE_ERROR_VENDOR1_' . $order_log_id, curl_error($curl));
                        foreach ($contact as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => curl_error($curl)
                            ]);
                        }

                        return;
                    }

                    setSetting('GP_RESPONSE_VENDOR_' . $order_log_id, json_encode($responseJSON));
                    if (isset($responseJSON['code'])) {
                        if ($responseJSON['code'] == 200) {
                            foreach ($contact as $key => $invoice) {
                                if (!$invoice->status_gp) {
                                    $invoice->update(['status_submit_gp' => 'submited']);
                                }

                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $invoice->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $invoice->id,
                                    'status' => 'success',
                                    'error_message' => null
                                ]);
                            }

                            return;
                        }
                    }

                    if (isset($responseJSON['desc'])) {
                        foreach ($contact as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['desc']
                            ]);
                        }
                    }
                } catch (ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    setSetting('GP_RESPONSE_ERROR_VENDOR2_' . $order_log_id, $responseBodyAsString);
                    foreach ($contact as $key => $purchase) {
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $purchase->id
                        ], [
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $purchase->id,
                            'status' => 'failed',
                            'error_message' => $responseBodyAsString
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }


    // get vendor lists
    public function getVendorList()
    {
        $vendor = DB::table('vendors')
            ->select('id', 'name as vendor_name', 'vendor_code')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $vendor,
            'message' => 'List Vendor'
        ]);
    }
}
