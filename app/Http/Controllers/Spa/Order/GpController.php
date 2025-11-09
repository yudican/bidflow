<?php

namespace App\Http\Controllers\Spa\Order;

use App\Http\Controllers\Controller;
use App\Jobs\Gp\SubmitMarketplaceQueue;
use App\Jobs\SubmitSIGpQueue;
use App\Jobs\TestQueue;
use App\Models\CompanyAccount;
use App\Models\InventoryProductStock;
use App\Models\LeadBilling;
use App\Models\MasterTax;
use App\Models\MPOrderList;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\PurchaseOrder;
use App\Models\SkuMaster;
use App\Models\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GpController extends Controller
{
    public function submitIndex($submit_id = null)
    {
        return view('spa.spa-index');
    }

    public function submitInvoiceSoGp(Request $request)
    {
        $data_submit = [];

        foreach ($request->billings as $key => $billingItem) {
            $billing = LeadBilling::find($billingItem['id']);
            $company = CompanyAccount::find($billing->company_id, ['account_code']);
            if ($billing) {
                $data_submit[] = [
                    'doc_date' => date('Y-m-d', strtotime($billing->approved_at)),
                    'customer_id' => $billingItem['contact_uid'],
                    'currency' => 'IDR',
                    'amount' => $billing->total_transfer,
                    'cashreceipt_type' => 0,
                    'checkbook_id' => $billing->account_bank,
                    'gl_posting_date' => date('Y-m-d'),
                    'comment' => 'Submit Invoice',
                ];
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'invoice-so',
                    'vat' => $request->vat_value,
                    'tax' => $request->tax_value,
                    'ref_id' => $billing->uid_lead
                ]);

                $body_value = ['header' => $data_submit];


                $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $orderSi->id, $body_value, $request->ids)->onQueue('queue-log');
                } else {
                    $order_log_id = $orderSi->id;
                    $orderSi->update(['body' =>  json_encode($body_value), 'company_id' => $company->account_code]);
                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/CR/CREntry',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => json_encode($body_value),
                            CURLOPT_HTTPHEADER => array(
                                'Content-Type: application/json',
                                'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);
                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_INVOICE_ERROR_' . $order_log_id, $response);
                            foreach ($request->billings as $key => $billingItem) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $billingItem['id']
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $billingItem['id'],
                                    'status' => 'failed',
                                    'error_message' => $response
                                ]);
                            }
                            $data_submit = [];
                            break;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_INVOICE_ERROR_' . $order_log_id, curl_error($curl));
                            foreach ($request->billings as $key => $billingItem) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $billingItem['id']
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $billingItem['id'],
                                    'status' => 'failed',
                                    'error_message' => curl_error($curl)
                                ]);
                            }

                            $data_submit = [];
                            break;
                        }

                        setSetting('GP_RESPONSE_INVOICE_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if (in_array($responseJSON['code'], [200, 201])) {
                                $orderLog = OrderSubmitLog::where('ref_id',  $billing->uid_lead)->where('type_si', 'invoice-so')->get();
                                foreach ($orderLog as $key => $logValue) {
                                    $logValue->update(['body' => null]);
                                }
                                $orderSi->update(['body' => null]);
                                foreach ($request->billings as $key => $billingItem) {
                                    $billing = LeadBilling::find($billingItem['id']);
                                    $billing->update(['gp_payment_number' => getNumberGP($responseJSON['data'][0]['success'], "/SI\/\d{4}\/\d+/")]);
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $billing->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $billing->id,
                                        'status' => 'success',
                                        'error_message' => null
                                    ]);
                                }

                                $data_submit = [];
                                break;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($request->billings as $key => $billingItem) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $billingItem['id']
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $billingItem['id'],
                                    'status' => 'failed',
                                    'error_message' => $responseJSON['desc']
                                ]);
                            }
                        }
                    } catch (ClientException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        setSetting('GP_RESPONSE_INVOICE_ERROR_' . $order_log_id, $responseBodyAsString);
                        foreach ($request->billings as $key => $billingItem) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $billingItem['id']
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $billingItem['id'],
                                'status' => 'failed',
                                'error_message' => $responseBodyAsString
                            ]);
                        }
                    }
                }
            }
            $data_submit = [];
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function submitGp(Request $request)
    {
        // $orders = null;

        // switch ($request->type) {
        //     case 'order-lead':
        //         $orders = OrderLead::query()->whereIn('uid_lead', $request->ids);
        //         break;
        //     case 'order-manual':
        //         $orders = OrderManual::query()->whereIn('uid_lead', $request->ids)->where('type', 'manual');
        //         break;

        //     default:
        $orders = OrderManual::query()->whereIn('uid_lead', $request->ids);
        //         break;
        // }

        $data_submit = [];

        $total_discount = 0;
        $total_tax = 0;
        foreach ($orders->get() as $key => $value) {
            $company = CompanyAccount::find($value->company_id, ['account_code']);
            foreach ($value->orderDelivery as $key => $product) {
                if ($product->is_invoice == 1 && !$value->gp_submit_number) {
                    $total_discount += $product->discount_amount;
                    $total_tax += $product->tax_invoiced;
                }
            }
            foreach ($value->orderDelivery as $key => $product) {
                if ($product->is_invoice == 1 && !$value->gp_submit_number) {
                    $data_submit[$value->id]['headers'][] = [
                        "SOPTYPE" => 3,
                        "DOCDATE" => date('Y-m-d', strtotime($value->created_at)),
                        "CUSTNMBR" => $value->contact_uid,
                        "BACHNUMB" => $value->contact_uid,
                        "CSTPONBR" => $value->invoice_number,
                        "TRDISAMT" => round($total_discount), // diisi diskon terbar
                        "FREIGHT" => 0,
                        "MISCAMNT" => 0
                    ];
                    $oldPrice = $product->subtotal_invoice; // 30000 / 3
                    $price = $oldPrice;
                    $unit_cost = $price;
                    if ($request->vat_value > 0) {
                        $unit_cost = $price / $request->vat_value;
                    }

                    $unit_cost += $total_tax;

                    $warehouse = null;

                    foreach ($request->products as $key => $product_value) {
                        if ($product_value['id'] == $product->id) {
                            $warehouse = $product_value['loc_node'];
                            break;
                        }
                    }

                    $data_submit[$value->id]['body'][] = [
                        "ITEMNMBR" => $this->convertSku($product->sku),
                        "CUSTNMBR" => $value->contact_uid,
                        "SOPTYPE" => 3,
                        "QUANTITY" => $product->qty_delivered,
                        "UOFM" => $product->productNeed->u_of_m,
                        "UNITCOST" => round($unit_cost),
                        "MRKDNAMT" => 0,
                        "LOCNCODE" => $warehouse,
                    ];

                    $warehouse = null;
                }
            }
            $total_discount = 0;
            $orderSi = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => $request->type,
                'vat' => $request->vat_value,
                'tax' => $request->tax_value,
                'ref_id' => $value->id
            ]);

            $body = [];
            foreach ($data_submit as $key => $itemData) {
                $body[] = json_encode([
                    'header' => $itemData['headers'],
                    'line' => $itemData['body'],
                ]);
            }

            $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
            foreach ($body as $key => $body_value) {
                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $orderSi->id, $body_value, $request->ids, $request->products)->onQueue('queue-log');
                } else {
                    $order_log_id = $orderSi->id;
                    $orderSi->update(['body' => $body_value, 'company_id' => $company->account_code]);
                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/SI/SIEntry',
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
                                'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_ERROR_' . $order_log_id, $response);
                            foreach ($orders->get() as $key => $ginee) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $ginee->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $ginee->id,
                                    'status' => 'failed',
                                    'error_message' => $ginee->contact_uid ? $response : 'Customer tidak ditemukan'
                                ]);
                            }
                            $data_submit = [];
                            break;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_ERROR_' . $order_log_id, curl_error($curl));
                            foreach ($orders->get() as $key => $ginee) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $ginee->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $ginee->id,
                                    'status' => 'failed',
                                    'error_message' => curl_error($curl)
                                ]);
                            }
                            $data_submit = [];
                            break;
                        }

                        setSetting('GP_RESPONSE_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if (in_array($responseJSON['code'], [200, 201])) {
                                $orderLog = OrderSubmitLog::where('ref_id', $value->id)->where('type_si', $request->type)->get();
                                foreach ($orderLog as $key => $logValue) {
                                    $logValue->update(['body' => null]);
                                }
                                $orderSi->update(['body' => null]);
                                foreach ($orders->get() as $key => $order) {
                                    if ($order->type == 'agent') {
                                        $order->where('status', 3);
                                    }
                                    $order->update(['status_submit' => 'submited', 'gp_si_number' => getNumberGP($responseJSON['data'][0]['success'])]);
                                    foreach ($order->orderDelivery as $key => $product) {
                                        if ($product->is_invoice == 1) {
                                            foreach ($request->products as $key => $product_value) {
                                                if ($product_value['id'] == $product->id) {
                                                    $product->update(['gp_submit_number' => getNumberGP($responseJSON['data'][0]['success'])]);
                                                }
                                            }
                                        }
                                    }
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $order->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $order->id,
                                        'status' => 'success',
                                        'error_message' => null
                                    ]);
                                }
                                $data_submit = [];
                                break;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($orders->get() as $key => $ginee) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $ginee->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $ginee->id,
                                    'status' => 'failed',
                                    'error_message' => $responseJSON['desc']
                                ]);
                            }
                        }
                    } catch (ClientException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        setSetting('GP_RESPONSE_ERROR_' . $order_log_id, $responseBodyAsString);
                        foreach ($orders->get() as $key => $ginee) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $ginee->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $ginee->id,
                                'status' => 'failed',
                                'error_message' => $responseBodyAsString
                            ]);
                        }
                    }
                }
            }
            $data_submit = [];
        }



        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function submitMarketPlace(Request $request)
    {
        $orders = MPOrderList::query()->whereIn('id', $request->ids);

        $data_submit = [];
        $skuList = SkuMaster::whereStatus(1)->get();
        setSetting('SUBMIT_PROGRESS_MP', 0);
        setSetting('SUBMIT_TOTAL_MP', count($request->ids));
        foreach ($orders->get() as $key => $value) {
            $data_submit[$value->id]['headers'][] = [
                "SOPTYPE" => 3,
                "DOCDATE" => date('Y-m-d', strtotime($value->created_at)),
                "CUSTNMBR" => $value->customer_code,
                "BACHNUMB" => time(),
                "CSTPONBR" => $value->trx_id,
                "FREIGHT" => round($value->shipping_fee),
                "TRDISAMT" => round($value->discount),
                "MISCAMNT" => round($value->mp_fee)
            ];

            foreach ($value->items as $key => $product) {
                $price = $product->final_price / $product->qty;
                $unit_cost = $price / 1.11;

                if ($request->tax_value > 0) {
                    $tax = $request->tax_value / 100;
                    $cost = $price * $tax;
                    $unit_cost += $cost;
                }

                $sku = $product->sku;
                $uofm = '-';

                foreach ($skuList as $index => $itemSku) {
                    $skus = explode('-', $sku);
                    if (isset($skus) && count($skus) > 1) {
                        if (str_contains($itemSku->sku, $skus[0])) {
                            $sku = $itemSku->sku;
                            $uofm = $itemSku->package_name;
                        }
                    } else {
                        if (str_contains($itemSku->sku, $product->sku)) {
                            $sku = $itemSku->sku;
                            $uofm = $itemSku->package_name;
                        }
                    }
                }

                $warehouse = null;

                foreach ($request->products as $key => $product_value) {
                    if ($product_value['id'] == $product->id) {
                        $warehouse = $product_value['loc_node'];
                        break;
                    }
                }

                $data_submit[$value->id]['body'][] = [
                    "ITEMNMBR" => $this->convertSku($sku),
                    "CUSTNMBR" => $value->customer_code,
                    "SOPTYPE" => 3,
                    "QUANTITY" => $product->qty,
                    "UOFM" => $uofm,
                    "UNITCOST" => round($unit_cost),
                    "MRKDNAMT" => 0,
                    "LOCNCODE" => $warehouse,
                ];

                $warehouse = null;
            }

            foreach ($data_submit as $key => $submitData) {
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'marketplace',
                    'vat' => $request->vat_value,
                    'tax' => $request->tax_value,
                    'ref_id' => $value->id
                ]);

                $body = json_encode([
                    'header' =>  $submitData['headers'],
                    'line' => $submitData['body'],
                ]);
                SubmitMarketplaceQueue::dispatch($body, $orderSi->id, $request->ids, $request->products)->onQueue('queue-log');
            }

            $data_submit = [];
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function submitTelmark(Request $request)
    {
        $orders = Transaction::query()->whereIn('id', $request->ids);

        $data_submit = [];
        $skuList = SkuMaster::whereStatus(1)->get();
        foreach ($orders->get() as $key => $value) {
            $data_submit[$value->id]['headers'][] = [
                "SOPTYPE" => 3,
                "DOCDATE" => date('Y-m-d', strtotime($value->created_at)),
                "CUSTNMBR" => 'NH-23001',
                "BACHNUMB" => time(),
                "CSTPONBR" => $value->invoice_number,
                "FREIGHT" => round($value->ongkir),
                "TRDISAMT" => round($value->diskon),
                "MISCAMNT" => 0
            ];

            foreach ($value->transactionDetail as $key => $product) {
                $price = $product->price;
                $unit_cost = $price;

                // if ($request->tax_value > 0) {
                //     $tax = $request->tax_value / 100;
                //     $cost = $price * $tax;
                //     $unit_cost += $cost;
                // }

                $sku = $product->productVariant->sku;
                $uofm = '-';

                foreach ($skuList as $index => $itemSku) {
                    $skus = explode('-', $sku);
                    if (isset($skus) && count($skus) > 1) {
                        if (str_contains($itemSku->sku, $skus[0])) {
                            $sku = $itemSku->sku;
                            $uofm = $itemSku->package_name;
                        }
                    } else {
                        if (str_contains($itemSku->sku, $product->productVariant->sku)) {
                            $sku = $itemSku->sku;
                            $uofm = $itemSku->package_name;
                        }
                    }
                }

                $warehouse = null;

                foreach ($request->products as $key => $product_value) {
                    if ($product_value['id'] == $product->transaction_id) {
                        $warehouse = $product_value['loc_node'];
                        break;
                    }
                }

                $data_submit[$value->id]['body'][] = [
                    "ITEMNMBR" => $this->convertSku($sku),
                    "CUSTNMBR" => 'NH-23001',
                    "SOPTYPE" => 3,
                    "QUANTITY" => $product->qty,
                    "UOFM" => $uofm,
                    "UNITCOST" => round($unit_cost),
                    "MRKDNAMT" => 0,
                    "LOCNCODE" => $warehouse,
                ];

                $warehouse = null;
            }

            foreach ($data_submit as $key => $submitData) {
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => $request->type == 'telmart' ? 'telmark' : 'trx_general',
                    'vat' => $request->vat_value,
                    'tax' => $request->tax_value,
                    'ref_id' => $value->id
                ]);

                $body = json_encode([
                    'header' =>  $submitData['headers'],
                    'line' => $submitData['body'],
                ]);
                $order_log_id = $orderSi->id;

                setSetting('GP_BODY_' . $order_log_id, $body);
                try {
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => getSetting('GP_URL') . '/SI/SIEntry',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $body,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . getSetting('GP_TOKEN_001')
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $responseJSON = json_decode($response, true);
                    // check is string
                    if (!$responseJSON && is_string($response)) {
                        setSetting('GP_RESPONSE_SUBMIT_TELMARK_ERROR_' . $order_log_id, $response);
                        foreach ($orders->get() as $key => $ginee) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $ginee->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $ginee->id,
                                'status' => 'failed',
                                'error_message' => $response
                            ]);
                        }
                    }

                    // Check if any error occured
                    if (curl_errno($curl)) {
                        setSetting('GP_RESPONSE_SUBMIT_TELMARK_ERROR_' . $order_log_id, curl_error($curl));
                        foreach ($orders->get() as $key => $ginee) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $ginee->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $ginee->id,
                                'status' => 'failed',
                                'error_message' => curl_error($curl)
                            ]);
                        }
                    }

                    setSetting('GP_RESPONSE_SUBMIT_TELMARK_' . $order_log_id, json_encode($responseJSON));
                    if (isset($responseJSON['code'])) {
                        if (in_array($responseJSON['code'], [200, 201])) {
                            foreach ($orders->get() as $key => $order) {
                                $order->update(['gp_submit_number' => getNumberGP($responseJSON['data'][0]['success'])]);

                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $order->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $order->id,
                                    'status' => 'success',
                                    'error_message' => null
                                ]);
                            }
                        }
                    }
                } catch (ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();

                    foreach ($orders->get() as $key => $ginee) {
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $ginee->id
                        ], [
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $ginee->id,
                            'status' => 'failed',
                            'error_message' => $responseBodyAsString
                        ]);
                    }
                }
            }

            $data_submit = [];
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function submitPoGp(Request $request)
    {
        $orders = PurchaseOrder::query()->whereIn('id', $request->ids);

        $data_submit = [];
        foreach ($orders->get() as $key => $value) {
            $company = CompanyAccount::find($value->company_id, ['account_code']);
            if ($value->gp_po_number) {
                return response()->json([
                    'message' => $value->po_number . ' - Data ini telah disubmit',
                    'status' => 'error',
                ], 400);
                $data_submit = [];
                break;
            }
            $tax = MasterTax::find($value->tax_id);
            $data_submit[$value->id]['headers'][] = [
                "VENDOR_CODE" => $value->vendor_code,
                "VENDOR_NAME" => $value->vendor_name,
                "PO_TYPE" => 1,
                "CURRENCY" =>  "IDR",
                "NOTES" => $value->po_number,
                "WAREHOUSE_ID" =>  $request->warehouse_id,
                "STATUS" => "COMPLETE",
                "TAX_ID" => $tax ? $tax->tax_code : ""
            ];
            foreach ($value->items as $key => $product) {
                if ($product->is_master == 1) {
                    $price = $product->price;
                    // $unit_cost = $price;
                    // if ($request->vat_value > 0) {
                    //     $unit_cost = $price / $request->vat_value;
                    // }

                    // if ($request->tax_value > 0) {
                    //     $tax = $request->tax_value / 100;
                    //     $cost = $price * $tax;
                    //     $unit_cost += $cost;
                    // }

                    $data_submit[$value->id]['body'][] = [
                        "PRODUCT_ID" => $this->convertSku($product->sku),
                        "UOM" =>   $product->u_of_m,
                        "PRICE" => round($price),
                        "QTY" => $product->qty
                    ];
                }
            }

            $submitLog = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => 'purchase-order',
                'vat' => $request->vat_value,
                'tax' => $request->tax_value,
                'ref_id' => $value->id,
            ]);

            $body = [];
            foreach ($data_submit as $key => $valueData) {
                $body[] = json_encode([
                    'header' => $valueData['headers'],
                    'line' => $valueData['body'],
                ]);
            }

            $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
            foreach ($body as $key => $body_value) {

                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
                } else {
                    $order_log_id = $submitLog->id;

                    $submitLog->update(['body' => ($body_value), 'company_id' => $company->account_code]);
                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/PO/POEntry',
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
                                'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_ERROR_PURCHASE_' . $order_log_id, $response);
                            foreach ($orders->get() as $key => $purchase) {
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

                            $data_submit = [];
                            break;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_ERROR_PURCHASE_' . $order_log_id, curl_error($curl));
                            foreach ($orders->get() as $key => $purchase) {
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

                            $data_submit = [];
                            break;
                        }

                        setSetting('GP_RESPONSE_PURCHASE_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if (in_array($responseJSON['code'], [200, 201])) {
                                $orderLog = OrderSubmitLog::where('ref_id', $value->id)->where('type_si', 'purchase-order')->get();
                                foreach ($orderLog as $key => $logValue) {
                                    $logValue->update(['body' => null]);
                                }
                                $submitLog->update(['body' => null]);

                                foreach ($orders->get() as $key => $order) {
                                    $order->update(['status_gp' => 'submited', 'tax_id' => $request->tax_id, 'submit_by' => auth()->user()->id, 'gp_po_number' => $responseJSON['data'][0]['ponumber']]);

                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $order->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $order->id,
                                        'status' => 'success',
                                        'error_message' => null
                                    ]);

                                    try {
                                        $submitLog->update([
                                            'ref_id' => $order->id,
                                        ]);
                                    } catch (\Throwable $th) {
                                        setSetting('GP_RESPONSE_PURCHASE_SAVE_LOG' . $order_log_id, $th->getMessage());
                                    }
                                }

                                $data_submit = [];
                                break;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($orders->get() as $key => $purchase) {
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
                        setSetting('GP_RESPONSE_ERROR_PURCHASE_' . $order_log_id, $responseBodyAsString);
                        foreach ($orders->get() as $key => $purchase) {
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

            $data_submit = [];
        }



        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    // submit receiving gp
    public function submitReceivingPoGp(Request $request)
    {
        $orders = null;
        $orders = PurchaseOrder::query()->whereIn('id', $request->ids);

        $data_submit = [];
        foreach ($orders->get() as $key => $value) {
            $company = CompanyAccount::find($value->company_id, ['account_code']);
            foreach ($value->items()->whereIn('id', $request->receivingIds)->get() as $key => $product) {
                $data_submit[$value->id]['headers'][] = [
                    "DO_NUMBER" =>  $product->do_number,
                    "VENDOR_CODE" => $value->vendor_code,
                    "VENDOR_NAME" => $value->vendor_name,
                ];

                $price = $product->price;
                $unit_cost = $price;
                if ($request->vat_value > 0) {
                    $unit_cost = $price / $request->vat_value;
                }

                if ($request->tax_value > 0) {
                    $tax = $request->tax_value / 100;
                    $cost = $price * $tax;
                    $unit_cost += $cost;
                }

                $data_submit[$value->id]['body'][] = [
                    "PURCHASE_ORDER_ID" => $value->gp_po_number,
                    "PRODUCT_ID" =>  $product->sku,
                    "UOM" =>   $product->u_of_m,
                    "PRICE" => round($unit_cost),
                    "QTY" => $product->qty_diterima
                ];
            }

            $submitLog = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => 'receiving-purchase-order',
                'vat' => $request->vat_value,
                'tax' => $request->tax_value,
            ]);

            $body = [];
            foreach ($data_submit as $key => $value) {
                $body[] = json_encode([
                    'header' => $value['headers'],
                    'line' => $value['body'],
                ]);
            }

            $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
            foreach ($body as $key => $body_value) {
                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
                } else {
                    $order_log_id = $submitLog->id;

                    $submitLog->update(['body' => $body_value, 'company_id' => $company->account_code]);
                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/Receiving/ReceivingEntry',
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
                                'Authorization: Bearer ' . getSetting('GP_TOKEN_' . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_ERROR_PURCHASE_' . $order_log_id, $response);
                            foreach ($orders->get() as $key => $purchase) {
                                foreach ($purchase->items()->whereIn('id', $request->receivingIds)->get() as $key => $product) {
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id,
                                        'status' => 'failed',
                                        'error_message' => $response
                                    ]);
                                }
                            }

                            $data_submit = [];
                            break;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_ERROR_PURCHASE_' . $order_log_id, curl_error($curl));
                            foreach ($orders->get() as $key => $purchase) {
                                foreach ($purchase->items()->whereIn('id', $request->receivingIds)->get() as $key => $product) {
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id,
                                        'status' => 'failed',
                                        'error_message' => curl_error($curl)
                                    ]);
                                }
                            }

                            $data_submit = [];
                            break;
                        }

                        setSetting('GP_RESPONSE_PURCHASE_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if (in_array($responseJSON['code'], [200, 201])) {
                                $orderLog = OrderSubmitLog::where('ref_id', $value->id)->where('type_si', 'receiving-purchase-order')->get();
                                foreach ($orderLog as $key => $logValue) {
                                    $logValue->update(['body' => null]);
                                }
                                $submitLog->update(['body' => null]);
                                foreach ($orders->get() as $key => $order) {
                                    foreach ($order->items()->whereIn('id', $request->receivingIds)->get() as $key => $product) {
                                        $product->update(['status_gp' => 'submited', 'gp_received_number' => $responseJSON['data'][0]['receivinG_NUMBER']]);
                                        OrderSubmitLogDetail::updateOrCreate([
                                            'order_submit_log_id' => $order_log_id,
                                            'order_id' => $product->id
                                        ], [
                                            'order_submit_log_id' => $order_log_id,
                                            'order_id' => $product->id,
                                            'status' => 'success',
                                            'error_message' => null
                                        ]);
                                        $submitLog->update([
                                            'ref_id' => $order->id,
                                            'child_id' => $product->id
                                        ]);
                                    }
                                }

                                $data_submit = [];
                                break;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($orders->get() as $key => $purchase) {
                                foreach ($purchase->items()->whereIn('id', $request->receivingIds)->get() as $key => $product) {
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id,
                                        'status' => 'failed',
                                        'error_message' => $responseJSON['desc']
                                    ]);
                                }
                            }
                        }
                    } catch (ClientException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        setSetting('GP_RESPONSE_ERROR_PURCHASE_' . $order_log_id, $responseBodyAsString);
                        foreach ($orders->get() as $key => $purchase) {
                            foreach ($purchase->items()->whereIn('id', $request->receivingIds)->get() as $key => $product) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $product->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $product->id,
                                    'status' => 'failed',
                                    'error_message' => $responseBodyAsString
                                ]);
                            }
                        }
                    }
                }
            }

            $data_submit = [];
        }




        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    // submit transfer entry
    public function submitTransferEntryGp(Request $request)
    {
        $orders = null;
        $orders = InventoryProductStock::query()->where('inventory_type', 'transfer')->whereIn('id', $request->ids);

        $data_submit = [];
        foreach ($orders->get() as $key => $value) {
            $company = CompanyAccount::find($value->company_id, ['account_code']);
            foreach ($value->detailItems as $key => $product) {
                $data_submit[$value->id]['headers'][] = [
                    "WAREHOUSE_ID" =>  $product->warehouse->wh_id,
                    "WAREHOUSE_DESTINATION" =>  $product->warehouseDestination->wh_id,
                    "NOTES" => $value->notes ?? '-',
                ];

                $data_submit[$value->id]['body'][] = [
                    "PRODUCT_ID" =>  $product->sku,
                    "UOM" =>   $product->u_of_m,
                    "QTY" => $product->qty_alocation
                ];
            }
            $submitLog = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => 'inventory-transfer',
                'vat' => $request->vat_value,
                'tax' => $request->tax_value,
                'ref_id' => $value->id
            ]);

            $body = [];
            foreach ($data_submit as $key => $value) {
                $body[] = json_encode([
                    'header' => $value['headers'],
                    'line' => $value['body'],
                ]);
            }

            $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
            foreach ($body as $key => $body_value) {
                if ($isUseQueue) {
                    SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
                } else {
                    $order_log_id = $submitLog->id;

                    $submitLog->update(['body' => $body_value, 'company_id' => $company->account_code]);
                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/Transfer/TransferEntry',
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
                                'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                            ),
                        ));

                        $response = curl_exec($curl);

                        curl_close($curl);

                        $responseJSON = json_decode($response, true);
                        // check is string
                        if (!$responseJSON && is_string($response)) {
                            setSetting('GP_RESPONSE_ERROR_TRANSFER_' . $order_log_id, $response);
                            foreach ($orders->get() as $key => $purchase) {
                                foreach ($purchase->detailItems as $key => $product) {
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id,
                                        'status' => 'failed',
                                        'error_message' => $response
                                    ]);
                                }
                            }
                            $data_submit = [];
                            break;
                        }

                        // Check if any error occured
                        if (curl_errno($curl)) {
                            setSetting('GP_RESPONSE_ERROR_TRANSFER_' . $order_log_id, curl_error($curl));
                            foreach ($orders->get() as $key => $purchase) {
                                foreach ($purchase->detailItems as $key => $product) {
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id,
                                        'status' => 'failed',
                                        'error_message' => curl_error($curl)
                                    ]);
                                }
                            }
                            $data_submit = [];
                            break;
                        }

                        setSetting('GP_RESPONSE_TRANSFER_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {

                            if (in_array($responseJSON['code'], [200, 201])) {
                                $orderLog = OrderSubmitLog::where('ref_id', $value->id)->where('type_si', 'inventory-transfer')->get();
                                foreach ($orderLog as $key => $logValue) {
                                    $logValue->update(['body' => null]);
                                }
                                $submitLog->update(['body' => null]);
                                foreach ($orders->get() as $key => $order) {
                                    $order->update(['status_gp' => 'submited']);
                                    foreach ($order->detailItems as $key => $product) {
                                        $product->update(['status_gp' => 'submited', 'gp_transfer_number' => $responseJSON['data'][0]['transfeR_NUMBER']]);
                                        OrderSubmitLogDetail::updateOrCreate([
                                            'order_submit_log_id' => $order_log_id,
                                            'order_id' => $product->id
                                        ], [
                                            'order_submit_log_id' => $order_log_id,
                                            'order_id' => $product->id,
                                            'status' => 'success',
                                            'error_message' => null
                                        ]);
                                    }
                                }
                                $data_submit = [];
                                break;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($orders->get() as $key => $purchase) {
                                foreach ($purchase->detailItems as $key => $product) {
                                    OrderSubmitLogDetail::updateOrCreate([
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id
                                    ], [
                                        'order_submit_log_id' => $order_log_id,
                                        'order_id' => $product->id,
                                        'status' => 'failed',
                                        'error_message' => $responseJSON['desc']
                                    ]);
                                }
                            }
                        }
                    } catch (ClientException $e) {
                        $response = $e->getResponse();
                        $responseBodyAsString = $response->getBody()->getContents();
                        setSetting('GP_RESPONSE_ERROR_TRANSFER_' . $order_log_id, $responseBodyAsString);
                        foreach ($orders->get() as $key => $purchase) {
                            foreach ($purchase->detailItems as $key => $product) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $product->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $product->id,
                                    'status' => 'failed',
                                    'error_message' => $responseBodyAsString
                                ]);
                            }
                        }
                    }
                }
            }

            $data_submit = [];
        }



        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function listSubmitGp(Request $request)
    {
        $search = $request->search;
        $type_si = $request->type;
        $created_at = $request->created_at;

        $orderLead =  OrderSubmitLog::query();
        if ($search) {
            $orderLead->where(function ($query) use ($search) {
                $query->whereHas('submitedBy', function ($sub_query) use ($search) {
                    return $sub_query->where('users.name', 'like', "%$search%");
                });
            });
        }

        if ($type_si) {
            $orderLead->whereIn('type_si', $type_si);
        }

        if ($request->status) {
            $orderLead->whereHas('orderSubmitLogDetails', function ($query) use ($request) {
                $query->whereIn('status', $request->status);
            });
        }

        if ($created_at) {
            // Assuming $created_at is an array with two elements: start date and end date
            $startDate = $created_at[0];
            $endDate = $created_at[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $orderLead->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orderLeads = $orderLead->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $orderLeads
        ]);
    }

    public function listSubmitGpDetail(Request $request, $submit_id)
    {
        $search = $request->search;

        $orderLead =  OrderSubmitLogDetail::query()->where('order_submit_log_id', $submit_id);
        if ($search) {
            $orderLead->where('error_message', 'like', "%$search%");
            $orderLead->orWhereHas('order', function ($query) use ($search) {
                $query->where('invoice_number', 'like', "%$search%");
            });
        }

        $orderLead->whereHas('orderSubmitLog', function ($query) use ($request) {
            return $query->whereIn('type_si', $request->type);
        });

        $orderLeads = $orderLead->orderBy('created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orderLeads
        ]);
    }

    public function convertSku($curentSku)
    {
        $skus  = [
            '8997230500863' => '19996230523',
            '8997230500344' => '19996230582',
            '8996293052050' => '8997230500924',
            '8997230500917' => '8997230500917',
            '6293512' => '6293512',
            '89962932831201' => '8996293283120',
            'S0001' => 'S0001',
            'fl100024' => 'S0007',
            '8996293218449' => '8996293218449',
            '8997236237312' => '8997236237312',
            '8997236236834' => '8997236236834',
            '8997236237077' => '8997236237077',
            '8997236237084' => '8997236237084',
            '8996293283126' => '8996293283120',
        ];

        if ($curentSku) {
            if (isset($skus[$curentSku])) {
                return $skus[$curentSku];
            }
            return $curentSku;
        }

        // return null;
        return $curentSku;
    }

    public function testGp() {}
}
