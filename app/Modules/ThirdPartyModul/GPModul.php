<?php

namespace App\Modules\ThirdPartyModul;

use App\Jobs\Gp\SubmitMarketplaceQueue;
use App\Jobs\SubmitSIGpQueue;
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
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class GPModul
{
    public function submitInvoiceSoGp($request)
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
                            CURLOPT_CUSTOM => 'POST',
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

    public function submitGp($request)
    {
        $orders = null;

        $leads = OrderLead::query()->whereIn('uid_lead', $request->ids);
        $manuals = OrderManual::query()->whereIn('uid_lead', $request->ids)->where('type', 'manual');

        $orders = $request->type == 'order-lead' ? $leads : $manuals;

        $data_submit = [];

        foreach ($orders->get() as $key => $value) {
            $company = CompanyAccount::find($value->company_id, ['account_code']);

            $data_submit[$value->id]['headers'][] = [
                "SOPTYPE" => 3,
                "DOCDATE" => date('Y-m-d', strtotime($value->created_at)),
                "CUSTNMBR" => $value->contact_uid,
                "BACHNUMB" => 00012023,
                "CSTPONBR" => $value->invoice_number,
                "TRDISAMT" => round($value->discount_amount),
                "FREIGHT" => round($value->ongkir),
                "MISCAMNT" => 0
            ];

            foreach ($value->orderDelivery as $key => $product) {
                if ($product->is_invoice == 1) {
                    $oldPrice = $product->productNeed->price_nego / $product->productNeed->qty; // 30000 / 3
                    $price = $oldPrice * $product->qty_delivered;
                    $unit_cost = $price;
                    if ($request->vat_value > 0) {
                        $unit_cost = $price / $request->vat_value;
                    }

                    if ($request->tax_value > 0) {
                        $tax = $request->tax_value / 100;
                        $cost = $price * $tax;
                        $unit_cost += $cost;
                    }

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
                            CURLOPT_CUSTOM => 'POST',
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
                                    $order->update(['status_submit' => 'submited']);
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

    public function submitMarketPlace($request)
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

    public function submitPoGp($request)
    {
        $orders = null;
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
                "TAX_ID" => $tax ? $tax->tax_code : "VAT IN"
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
                        "PRODUCT_ID" =>  $product->sku,
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
                            CURLOPT_CUSTOM => 'POST',
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
    public function submitReceivingPoGp($request)
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
                            CURLOPT_CUSTOM => 'POST',
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
    public function submitTransferEntryGp($request)
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
                            CURLOPT_CUSTOM => 'POST',
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

    // submit invoice entry gp
    public function submitInvoiceEntryGp(Request $request)
    {
        $invoiceEntry = PurchaseInvoiceEntry::whereIn('id', $request->items)->get();
        $header = [];
        $line = [];
        $data_submit = [];
        foreach ($invoiceEntry as $key => $item) {
            $company_id = ($item->company_id) ? $item->company_id : auth()->user()->company_id;
            $company = CompanyAccount::find($company_id, ['account_code']);
            $tax = MasterTax::find($item->tax_id);
            if ($item->status_gp == "submited") {
                return response()->json([
                    'message' => 'Data invoice entry sudah pernah di submit',
                    'status' => 'failed',
                ]);
            }
            $paymentTerm = PaymentTerm::find($item->payment_term_id);

            if ($item->type_invoice == 'product') {
                $data_submit[$item->id]['headers'][] =
                    [
                        'VENDOR_NUMBER' => $item->vendor_doc_number,
                        'VENDOR_ID' => $item->vendor_id,
                        'VENDOR_NAME' => $item->vendor_name,
                        'INVOICE_DATE' => $item->invoice_date,
                        'PAYMENT_TERM' => $paymentTerm->name,
                        'TAX_ID' => @$tax->tax_code ?? 'VAT IN',
                    ];

                $invoiceEntryItem = PurchaseInvoiceEntryItem::where('purchase_invoice_entry_id', $item->id)->get();
                foreach ($invoiceEntryItem as $key => $row) {
                    $poItem = PurchaseOrderItem::find($row->purchase_order_item_id);
                    $purchase = PurchaseOrder::find($row->purchase_order_id);
                    $product = Product::find($row->product_id);

                    $data_submit[$item->id]['body'][] =
                        [
                            'PO_NUMBER' => $purchase->gp_po_number,
                            'RECEIVING_NUMBER' => $poItem->gp_received_number,
                            'PRODUCT_ID' => $product->sku,
                            'UOM' => $row->uom,
                            'QTY' => $row->qty,
                        ];
                }

                $submitLog = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'purchasing-invoice-entry',
                    'vat' => $request->vat_value,
                    'tax' => $item->tax_id ?? 'VAT IN',
                    'ref_id' => $item->id
                ]);
            } else {
                $extended_cost = 0;
                $qty = 0;
                $invoiceEntryItem = PurchaseInvoiceEntryItem::where('purchase_invoice_entry_id', $item->id)->get();
                foreach ($invoiceEntryItem as $key => $row) {
                    $extended_cost += $row->extended_cost;
                    $qty += $row->qty;
                }
                $data_submit[$item->id]['headers'][] =
                    [
                        'DOCUMENT_TYPE' => '1',
                        'VENDOR_ID' => $item->vendor_id,
                        'VENDOR_NAME' => $item->vendor_name,
                        'CURRENCY' => 'IDR',
                        'DOC_NUMBER' => $item->vendor_doc_number,
                        'TYPE_AMOUNT' => $qty * $extended_cost,
                        'TRADE_DISCOUNT' => 0,
                        'FREIGHT' => 0,
                        'MISCELLANNOUS' => 0,
                        'TAX_ID' => @$tax->tax_code ?? 'VAT IN',
                    ];

                $submitLog = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'payables-entry', //jasa
                    'vat' => $request->vat_value,
                    'tax' => @$item->tax_id ?? 'VAT IN',
                    'ref_id' => $item->id
                ]);
            }
        }

        if ($item->type_invoice == 'product') {
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

                    setSetting('GP_BODY_' . $order_log_id, $body_value);

                    try {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => getSetting('GP_URL') . '/PI/PIEntry',
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
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $response);
                            foreach ($invoiceEntry as $key => $purchase) {
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
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, curl_error($curl));
                            foreach ($invoiceEntry as $key => $purchase) {
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

                        setSetting('GP_RESPONSE_INVOICE_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if ($responseJSON['code'] == 200) {
                                foreach ($invoiceEntry as $key => $invoice) {
                                    if (!$invoice->status_gp) {
                                        $invoice->update(['status_gp' => 'submited', 'gp_invoice_number' => $responseJSON['data'][0]['invoicE_NUMBER']]);
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
                                    $submitLog->update(['ref_id' => $invoice->id]);
                                }
                                return;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($invoiceEntry as $key => $purchase) {
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
                        setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $responseBodyAsString);
                        foreach ($invoiceEntry as $key => $purchase) {
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
        } else {
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
                            CURLOPT_URL => getSetting('GP_URL') . '/PTE/PTEEntry',
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
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $response);
                            foreach ($invoiceEntry as $key => $purchase) {
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
                            setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, curl_error($curl));
                            foreach ($invoiceEntry as $key => $purchase) {
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

                        setSetting('GP_RESPONSE_PAYABLES_' . $order_log_id, json_encode($responseJSON));
                        if (isset($responseJSON['code'])) {
                            if ($responseJSON['code'] == 200) {
                                foreach ($invoiceEntry as $key => $invoice) {
                                    if (!$invoice->status_gp) {
                                        $invoice->update(['status_gp' => 'submited', 'status_payable_gp' => 'submited', 'gp_invoice_number' => $responseJSON['data'][0]['voucheR_NUMBER'], 'gp_payable_number' => $responseJSON['data'][0]['voucheR_NUMBER']]);
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

                                    $submitLog->update(['ref_id' => $invoice->id]);
                                }

                                return;
                            }
                        }

                        if (isset($responseJSON['desc'])) {
                            foreach ($invoiceEntry as $key => $purchase) {
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
                        setSetting('GP_RESPONSE_ERROR_PAYABLES_' . $order_log_id, $responseBodyAsString);
                        foreach ($invoiceEntry as $key => $purchase) {
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
        }

        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    // submit payment gp
    public function submitPaymentGp(Request $request)
    {
        $invoiceEntry = PurchaseInvoiceEntry::whereIn('id', $request->items)->get();
        $billings = PurchaseBilling::whereIn('id', $request->billingIds)->whereNull('status_gp')->get();
        $body = [];
        foreach ($billings as $bill) {
            $company_id = ($bill->company_id) ? $bill->company_id : auth()->user()->company_id;
            $company = CompanyAccount::find($company_id, ['account_code']);
            if ($bill->status == 1) {
                $po = PurchaseOrder::find($bill->purchase_order_id);
                $inv_entry = PurchaseInvoiceEntry::where('received_number', $bill->received_number)->first();

                $body[] =
                    [
                        'PURCHASE_ORDER_ID' => @$po->gp_po_number,
                        'VENDOR_CODE' => (!empty($po) ? $po->vendor_code : $inv_entry->vendor_id),
                        'VENDOR_NAME' => (!empty($po) ? $po->vendor_name : $inv_entry->vendor_name),
                        'PAYMENT_DATE' => date('Y-m-d', strtotime($bill->created_at)),
                        'NAMA_BANK' => $bill->nama_bank,
                        'JUMLAH_TRANSFER' => $bill->jumlah_transfer,
                        'TYPE_TAX' => ($inv_entry->type_invoice == 'jasa') ? 1 : 0,
                    ];

                $submitLog = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'manual-payment-entry',
                    'vat' => '',
                    'tax' => '',
                    'ref_id' => $bill->id
                ]);
            }
        }

        $isUseQueue = getSetting('GP_SUBMIT_QUEUE');

        foreach ($body as $key => $body_value) {
            if ($isUseQueue) {
                SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body, $request->ids)->onQueue('queue-log');
            } else {
                $order_log_id = $submitLog->id;

                setSetting('GP_BODY_' . $order_log_id, json_encode($body_value));

                try {
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => getSetting('GP_URL') . '/PMP/PMPEntry',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $body_value,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: multipart/form-data', // Mengubah Content-Type
                            'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $responseJSON = json_decode($response, true);
                    // check is string
                    if (!$responseJSON && is_string($response)) {
                        setSetting('GP_RESPONSE_ERROR_BILLING_' . $order_log_id, $response);
                        foreach ($invoiceEntry as $key => $purchase) {
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
                        setSetting('GP_RESPONSE_ERROR_BILLING_' . $order_log_id, curl_error($curl));
                        foreach ($invoiceEntry as $key => $purchase) {
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

                    setSetting('GP_RESPONSE_BILLING_' . $order_log_id, json_encode($responseJSON));
                    if (isset($responseJSON['code'])) {
                        if ($responseJSON['code'] == 201) {
                            foreach ($invoiceEntry as $key => $invoice) {
                                $billings = PurchaseBilling::where('received_number', $invoice->received_number)->get();
                                foreach ($billings as $bill) {
                                    if (!$bill->status_gp) {
                                        $bill->update(['status_gp' => 'submited', 'gp_payment_number' => $responseJSON['data'][0]['paymenT_NUMBER']]);
                                    }
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

                                $submitLog->update(['ref_id' => $bill->id]);
                            }

                            return;
                        }
                    }

                    if (isset($responseJSON['desc'])) {
                        foreach ($invoiceEntry as $key => $purchase) {
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
                    setSetting('GP_RESPONSE_ERROR_PAYMENT_' . $order_log_id, $responseBodyAsString);
                    foreach ($invoiceEntry as $key => $purchase) {
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

    // submit contact gp
    public function submitContactGp(Request $request)
    {
        if ($request->items[0] == "") {
            return response()->json([
                'message' => 'Data customer code masih kosong',
                'status' => 'failed',
            ]);
        }
        $contact = User::whereIn('uid', $request->items)->get();
        $company = CompanyAccount::find(auth()->user()->company_id, ['account_code']);
        $header = [];
        $line = [];
        $data_submit = [];
        foreach ($contact as $key => $item) {
            if ($item->status_gp == "submited") {
                return response()->json([
                    'message' => 'Data contact sudah pernah di submit',
                    'status' => 'failed',
                ]);
            }

            $data_submit[$item->uid]['headers'][] =
                [
                    'ID' => $item->uid,
                    'NAME' => $item->name,
                    'EMAIL' => $item->email,
                    'TELEPON' => $item->telepon,
                ];
        }

        $submitLog = OrderSubmitLog::create([
            'submited_by' => auth()->user()->id,
            'type_si' => 'customer-contact',
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
                        CURLOPT_URL => getSetting('GP_URL') . '/Customer/CustomerEntry',
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
                        setSetting('GP_RESPONSE_ERROR_INVOICE_' . $order_log_id, $response);
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
                        setSetting('GP_RESPONSE_ERROR_CONTACT_' . $order_log_id, curl_error($curl));
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

                    setSetting('GP_RESPONSE_CONTACT_' . $order_log_id, json_encode($responseJSON));
                    if (isset($responseJSON['code'])) {
                        if ($responseJSON['code'] == 200) {
                            foreach ($contact as $key => $invoice) {
                                if (!$invoice->status_gp) {
                                    $invoice->update(['status_gp' => 'submited']);
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
                    setSetting('GP_RESPONSE_ERROR_CONTACT_' . $order_log_id, $responseBodyAsString);
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

    // convert sku
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
}
