<?php

namespace App\Http\Controllers\Spa\Order;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\OrderDelivery;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class OrderInvoiceController extends Controller
{
    public function invoiceIndex($submit_id = null)
    {
        return view('spa.spa-index');
    }

    public function listOrderInvoice(Request $request)
    {
        $search = $request->search;
        $invoice_date = $request->invoice_date;
        $status = $request->status;
        $user = auth()->user();
        $role = $user->role->role_type;
        $account_id = $request->account_id;
        $orderLead =  DB::table('vw_sales_orders_invoices')->where('is_invoice', 1);

        if ($search) {
            $orderLead->where(function ($query) use ($search) {
                $query->where('invoice_number', 'like', "%$search%");
                $query->orWhere('no_faktur', 'like', "%$search%");
                $query->orWhere('delivery_number', 'like', "%$search%");
                $query->orWhere('gp_submit_number', 'like', "%$search%");
            });
        }

        if ($status) {
            $orderLead->whereIn('status', $status);
        }

        if ($invoice_date) {
            $orderLead->whereBetween('invoice_date', $invoice_date);
        }

        // cek switch account
        if ($account_id) {
            $orderLead->where('company_id', $account_id);
        }


        $orderLeads = $orderLead->orderBy('created_at', 'desc')->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(invoice_number, '/', -1), '/', 1) AS UNSIGNED) DESC")->groupBy('uid_invoice')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orderLeads,
        ]);
    }

    public function getOrderDetail($uid_lead, $uid_delivery)
    {
        $orderLead = null;

        $delivery_check = DB::table('order_deliveries')->where('uid_invoice', $uid_delivery)->first();
        $delivery = null;
        $uid_invoice = $delivery_check?->uid_invoice ?? null;
        $history = OrderSubmitLog::where('ref_id', $uid_lead)->where('type_si', 'reset-si-gp')->get();
        if ($delivery_check && $delivery_check->type_so == 'order-konsinyasi') {
            $uid_lead = OrderDelivery::where('uid_invoice', $uid_invoice)->groupBy('uid_lead')->pluck('uid_lead')->toArray();
        } else {
            $delivery = OrderDelivery::where('uid_lead', $uid_lead)->first();
            $uid_invoice = $delivery ? $delivery->uid_invoice : $delivery->id;
            $uid_lead = $delivery ? $delivery->uid_lead : null;
        }

        $orderLead = (array) DB::table('order_manuals as om')
            ->leftJoin('users as u', 'u.id', '=', 'om.contact')
            ->leftJoin('users as su', 'su.id', '=', 'om.sales')
            ->leftJoin('users as cu', 'cu.id', '=', 'om.user_created')
            ->leftJoin('users as cou', 'cou.id', '=', 'om.courier')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'om.payment_term')
            ->leftJoin('company_accounts as ca', 'om.company_id', '=', 'ca.id')
            ->leftJoin('warehouses as wh', 'om.warehouse_id', '=', 'wh.id')
            ->where('om.uid_lead', $uid_lead)
            ->select(
                'om.id',
                'om.created_at',
                'om.order_number',
                'om.invoice_number',
                'om.uid_lead',
                'om.contact',
                'om.sales',
                'om.payment_term',
                'om.company_id',
                'om.user_created',
                'u.name as contact_name',
                'su.name as sales_name',
                'cu.name as created_by_name',
                'pt.name as payment_term_name',
                'ca.account_name as company_name',
                'cou.name as courier_name',
                'wh.name as warehouse_name',
                'om.customer_need',
                'om.subtotal',
                'om.dpp',
                'om.diskon as discount_amount',
                'om.ppn',
                'om.total as total_amount',
            )
            ->first();

        if ($orderLead) {
            $data = [
                'invoice_number' => $delivery_check->invoice_number ?? $delivery?->invoice_number,
                'status_invoice' => $delivery_check->status_invoice ?? $delivery?->status_invoice ?? 'Belum Bayar',
                'no_faktur' => $delivery_check->no_faktur ?? $delivery?->no_faktur,
                'order_delivery' => [],
                'product_needs' => [],
                'invoice_date' => $delivery_check->invoice_date ?? $delivery?->invoice_date,
                'histories' => $history ?? [],
                'type_so' => $delivery_check->type_so,
            ];

            return response()->json([
                'status' => 'success',
                'data' => array_merge($orderLead, $data),
                'print' => [
                    'si' => route('print.si', $uid_invoice),
                ],
            ]);
        }



        return response()->json([
            'status' => 'success',
            'data' => [],
            'print' => [
                'si' => route('print.si', $uid_lead),
            ]
        ], 400);
    }

    public function getOrderDelivery($uid_lead)
    {
        if ($uid_lead) {
            $orderDelivery = OrderDelivery::where('uid_lead', $uid_lead)->get();

            return response()->json([
                'status' => 'success',
                'data' => $orderDelivery
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function submitInvoice(Request $request)
    {
        try {
            DB::beginTransaction();
            if (is_array($request->uid_lead)) {
                $orderDeliverys = OrderDelivery::whereIn('uid_lead', $request->uid_lead)->where('is_invoice', 0)->get();
                $delivery =  OrderDelivery::find($request->items[0], ['type_so']);
                $orderManual = DB::table('order_manuals')->where('uid_lead', $request?->uid_lead ?? $delivery->uid_lead)->select('id', 'type_so')->first();
                $invoice_number = OrderDelivery::generateInvoiceNumber($delivery?->type_so, $orderManual?->id ?? date('dHis'));
                $delivery_number = OrderDelivery::generateDeliveryNumberNumber();
                $uid_invoice = generateUid();
                foreach ($orderDeliverys as $key => $delivery) {
                    if (in_array($request->type_so, ['order-manual', 'freebies', 'konsinyasi'])) {
                        $orderManual = OrderManual::where('uid_lead', $delivery->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => Carbon::now()]);
                        }
                    } else {
                        $orderManual = OrderLead::where('uid_lead', $delivery->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => Carbon::now()]);
                        }
                    }
                    $delivery->update(['type_so' => $delivery->type_so, 'is_invoice' => 1, 'uid_invoice' => $uid_invoice, 'invoice_number' => $invoice_number, 'delivery_number' => $delivery_number, 'invoice_date' => Carbon::now()]);
                }
            } else {
                if ($request->items && is_array($request->items)) {
                    $orderDeliverys = OrderDelivery::whereIn('id', $request->items)->get();
                    $invoice_number = OrderDelivery::generateInvoiceNumber($request->type_so ?? 'order-manual', date('dHis'));
                    $delivery_number = OrderDelivery::generateDeliveryNumberNumber();
                    $uid_invoice = hash('crc32', Carbon::now()->format('U'));
                    $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
                    if ($orderManual) {
                        $orderManual->update(['invoice_date' => Carbon::now()]);
                    }

                    foreach ($orderDeliverys as $key => $value) {
                        $value->update(['type_so' => $request->type_so, 'is_invoice' => 1, 'uid_invoice' => $uid_invoice, 'invoice_number' => $invoice_number, 'delivery_number' => $delivery_number, 'invoice_date' => Carbon::now()]);
                    }
                }
            }


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Gagal Disimpan',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function updateInvoiceDate(Request $request)
    {

        $time = date(' H:i:s');
        $invoice_date = date('Y-m-d', strtotime($request->invoice_date)) . $time;
        try {
            DB::beginTransaction();
            if ($request->delivery_id) {
                $orderDelivery = OrderDelivery::find($request->delivery_id);
                if ($orderDelivery) {
                    if ($orderDelivery->type_so == 'order-manual' || $orderDelivery->type_so == 'freebies') {
                        $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    } else {
                        $orderManual = OrderLead::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    }

                    $orderDelivery->update(['invoice_date' => $invoice_date]);
                } else {
                    if ($request->type_so == 'order-manual' || $request->type_so == 'freebies') {
                        $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    } else {
                        $orderManual = OrderLead::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    }
                }
            } else {
                $orderDelivery = OrderDelivery::where('uid_lead', $request->uid_lead)->first();
                if ($orderDelivery && $orderDelivery->type_so) {
                    if ($orderDelivery->type_so == 'order-manual' || $orderDelivery->type_so == 'freebies') {
                        $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    } else {
                        $orderManual = OrderLead::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    }

                    $orderDelivery->update(['invoice_date' => $invoice_date]);
                } else {
                    if ($request->type_so == 'order-manual' || $request->type_so == 'freebies') {
                        $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    } else {
                        $orderManual = OrderLead::where('uid_lead', $request->uid_lead)->first();
                        if ($orderManual) {
                            $orderManual->update(['invoice_date' => $invoice_date]);
                        }
                    }
                }
            }


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice Date Berhasil disimpan',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice Date Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function cancelInvoice(Request $request, $invoice_id)
    {
        if (!$invoice_id) {
            return response()->json(['message' => 'Invoice Tidak Ditemukan'], 400);
        }
        try {
            DB::beginTransaction();
            $orderDelivery = OrderDelivery::find($invoice_id, ['id', 'type_so']);
            if ($orderDelivery) {
                if ($orderDelivery->type_so == 'order-manual' || $orderDelivery->type_so == 'freebies') {
                    $orderManual = OrderManual::where('uid_lead', $request->uid_lead)->first();
                    if ($orderManual) {
                        $orderManual->update(['invoice_date' => null]);
                    }
                } else {
                    $orderManual = OrderLead::where('uid_lead', $request->uid_lead)->first();
                    if ($orderManual) {
                        $orderManual->update(['invoice_date' => null]);
                    }
                }

                $orderInvoices = OrderDelivery::where(['uid_invoice' => $request->uid_invoice])->get();
                if (count($orderInvoices) > 0) {
                    foreach ($orderInvoices as $key => $invoice) {
                        $invoice->update(['invoice_date' => null, 'is_invoice' => 0, 'invoice_number' => null, 'uid_invoice' => null, 'delivery_date' => null, 'delivery_number' => null]);
                    }
                } else {
                    $orderDelivery->update(['invoice_date' => null, 'is_invoice' => 0, 'invoice_number' => null, 'uid_invoice' => null, 'delivery_date' => null, 'delivery_number' => null]);
                }


                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invoice Date Berhasil dibatalkan',
                ]);
            }
            return response()->json(['message' => 'Invoice Tidak Ditemukan'], 400);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice Date Gagal Dibatalkan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function submitSingleKlikpajak(Request $request, $product_need_id = null)
    {
        $url = 'https://sandbox-api.mekari.com/v2/klikpajak/v1/efaktur/out?auto_approval=false&auto_calculate=true';

        try {
            DB::beginTransaction();

            $orderDelivery = OrderDelivery::find($request->invoice_id);
            // $row = ProductNeed::find($product_need_id);
            // $product = ProductVariant::find($row->product->id);
            // $order = OrderManual::where('uid_lead', $orderDelivery->uid_lead)->first();
            // $customer = User::find($order->contact);
            // $company = Company::where('user_id', $order->contact)->first();
            $invoice_number = OrderDelivery::generateInvoiceNumber();
            $uid_invoice = hash('crc32', Carbon::now()->format('U'));
            // if ($company->need_faktur) {
            //     $invoice_number = OrderDelivery::generateInvoiceNumber();
            //     //submit klik pajak
            //     $efakturData = [
            //         "client_reference_id" =>  $orderDelivery->invoice_number ?? $invoice_number,
            //         "transaction_detail" => "02",
            //         "additional_trx_detail" => "00",
            //         "substitution_flag" => false,
            //         "substituted_faktur_id" => null,
            //         "document_number" => null,
            //         "document_date" => date('Y-m-d'),
            //         "reference" =>  $orderDelivery->invoice_number ?? $invoice_number,
            //         "total_dpp" => 0,
            //         "total_ppn" => 0,
            //         "total_ppnbm" => 0,
            //         "downpayment_flag" => null,
            //         "downpayment_dpp" => 0,
            //         "downpayment_ppn" => 0,
            //         "downpayment_ppnbm" => 0,
            //         "customer" => [
            //             "name" => @$customer->name,
            //             "npwp" => @$company->npwp,
            //             "nik" => "9876543210123456",
            //             "address" => "Customer Address",
            //             "email" => @$customer->email
            //         ],
            //         "items" => [
            //             [
            //                 "name" => $product->name,
            //                 "unit_price" => $orderDelivery->price_product,
            //                 "quantity" => $orderDelivery->qty_delivered,
            //                 "discount" => 0,
            //                 "ppnbm_rate" => 0,
            //                 "dpp" => 0,
            //                 "ppn" => 0,
            //                 "ppnbm" => 0
            //             ]
            //         ]
            //     ];

            //     setSetting('BODY_KLIK_PAJAK', json_encode($efakturData));
            //     // Generate header HMAC
            //     $headers = $this->generateHmacHeader('POST', '/v2/klikpajak/v1/efaktur/out', json_encode($efakturData));
            //     $response = Http::withHeaders($headers)->post($url, $efakturData, ['allow_redirects' => false]);
            //     $responseJson = $response->json();
            //     setSetting('SUCCESS_SUBMIT_KLIK_PAJAK', json_encode($responseJson));
            //     if (!empty($response) && @$responseJson['code'] == '00') {
            //         $orderDelivery->update(['submit_klikpajak' => 'submitted', 'no_faktur' => $responseJson['data']['document_number']]);
            //     }
            // }

            if ($orderDelivery) {
                $invoice_date = Carbon::now();
                $orderDelivery->update(['is_invoice' => 1, 'invoice_number' => $orderDelivery->invoice_number ?? $invoice_number, 'uid_invoice' => $uid_invoice, 'invoice_date' => $invoice_date]);


                if (in_array($orderDelivery->type_so, ['order-manual', 'freebies', 'konsinyasi'])) {
                    $orderManual = OrderManual::where('uid_lead', $orderDelivery->uid_lead)->first();
                    if ($orderManual) {
                        $orderManual->update(['invoice_date' => $invoice_date]);
                    }
                } else {
                    $orderManual = OrderLead::where('uid_lead', $orderDelivery->uid_lead)->first();
                    if ($orderManual) {
                        $orderManual->update(['invoice_date' => $invoice_date]);
                    }
                }
            }


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    public function submitBulkKlikpajak(Request $request, $submit = true)
    {
        $url = 'https://sandbox-api.mekari.com/v2/klikpajak/v1/efaktur/out?auto_approval=false&auto_calculate=true';

        try {
            DB::beginTransaction();
            $uid_invoice = hash('crc32', Carbon::now()->format('U'));
            $invoice_number = OrderDelivery::generateInvoiceNumber('order-manual', date('dHis'));
            if ($submit) {
                $orderDeliverys = OrderDelivery::whereIn('uid_lead', $request->items)->whereNull('no_faktur')->get();
                foreach ($orderDeliverys as $key => $delivery) {
                    $items = [];
                    $order = OrderManual::where('uid_lead', $delivery->uid_lead)->first(['uid_lead', 'contact', 'invoice_number']);
                    if ($delivery->type_so == 'order-lead') {
                        $order = OrderLead::where('uid_lead', $delivery->uid_lead)->first(['uid_lead', 'contact', 'invoice_number']);
                    }

                    $customer = User::find($order->contact, ['id', 'appendix', 'company_id']);
                    $company = Company::where('user_id', $order->contact)->first(['need_faktur', 'npwp', 'npwp_name', 'name', 'address', 'email', 'nik']);

                    if ($company && $company->need_faktur) {
                        if (empty($company->npwp)) {
                            return response()->json([
                                'message' => 'Submit Gagal, contact tidak memiliki npwp'
                            ], 400);
                        } else if (empty($company->nik && strlen($company->nik) < 16)) {
                            return response()->json([
                                'message' => 'Submit Gagal, contact tidak memiliki nik atau format nik tidak sesuai'
                            ], 400);
                        } else {
                            $orderDeliveryItems = DB::table('vw_sales_orders_delivery_items')->where('uid_invoice', $delivery->uid_invoice)->get();

                            foreach ($orderDeliveryItems as $key => $deliveryItem) {
                                $price = $deliveryItem->total;
                                $type = $delivery->type_so;
                                $unit_price = $type == 'freebies' ? 1 : round($deliveryItem->price_item, 5);
                                $items[] = [
                                    "name" => $deliveryItem->product_name,
                                    "unit_price" => $unit_price,
                                    "quantity" => $deliveryItem->qty_delivered,
                                    "discount" => $deliveryItem->discount_amount ?? 0,
                                    "ppnbm_rate" => 0,
                                    "dpp" => 0,
                                    "ppn" => $deliveryItem->tax_amount > 0 ? round($deliveryItem->tax_amount / $deliveryItem->qty_delivered, 5) : 0,
                                    "ppnbm" => 0
                                ];
                            }

                            $body = [
                                "client_reference_id" => $delivery->invoice_number ?? $invoice_number,
                                "transaction_detail" => $customer->appendix,
                                "additional_trx_detail" => "00",
                                "substitution_flag" => false,
                                "substituted_faktur_id" => null,
                                "document_number" => null,
                                "document_date" => date('Y-m-d'),
                                "reference" => $delivery->invoice_number ?? $invoice_number,
                                "total_dpp" => 0,
                                "total_ppn" => 0,
                                "total_ppnbm" => 0,
                                "downpayment_flag" => null,
                                "downpayment_dpp" => 0,
                                "downpayment_ppn" => 0,
                                "downpayment_ppnbm" => 0,
                                "customer" => [
                                    "name" => @$company->npwp_name ?? '-',
                                    "npwp" => @$company->npwp,
                                    "nik" => @$company->nik,
                                    "address" => $company->address ?? '-',
                                    "email" => @$company->email
                                ],
                                "items" => $items
                            ];

                            setSetting('BODY_KLIK_PAJAK', json_encode($body));
                            // Generate header HMAC
                            $headers = $this->generateHmacHeader('POST', '/v2/klikpajak/v1/efaktur/out', json_encode($body));
                            $response = Http::withHeaders($headers)->post($url, $body, ['allow_redirects' => false]);
                            $responseJson = $response->json();
                            setSetting('SUCCESS_SUBMIT_KLIK_PAJAK', json_encode($responseJson));
                            if (!empty($response)) {
                                if (@$responseJson['code'] == '00') {
                                    foreach ($orderDeliveryItems as $key => $deliveryItem) {
                                        OrderDelivery::find($deliveryItem->id)->update(['submit_klikpajak' => 'submitted', 'no_faktur' => $responseJson['data']['document_number']]);
                                    }
                                }
                                if (@$responseJson['code'] == '1000') {
                                    return response()->json([
                                        'message' => @$responseJson['message']
                                    ], 400);
                                }
                            }

                            $items = [];
                        }
                    } else {
                        return response()->json([
                            'message' => 'Submit Gagal, status faktur tidak mendukung untuk klikpajak'
                        ], 400);
                    }
                }
            } else {
                $orderDeliverys = OrderDelivery::whereIn('id', $request->items)->whereNull('no_faktur')->get();
                foreach ($orderDeliverys as $key => $delivery) {
                    OrderDelivery::find($delivery->id)->update(['is_invoice' => 1, 'invoice_number' => $delivery->invoice_number ?? $invoice_number, 'uid_invoice' => $uid_invoice, 'invoice_date' => Carbon::now()]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            setSetting('ERROR_SUBMIT_KLIK_PAJAK', $th->getMessage());
        }
    }

    public function resetGp(Request $request)
    {
        $sales_invoices = $request->items;
        $lists = [];
        $list_errors = [];

        foreach ($sales_invoices as $si) {
            $orderItem = OrderDelivery::where('uid_lead', $si)->first(['invoice_number']);
            if ($orderItem) {
                $lists[] = $si;
                OrderDelivery::where('uid_lead', $si)->update(['gp_submit_number' => null]);
                $orderItem->orderManual()->update(['status_submit' => 'notsubmited']);
                $orderDel = OrderDelivery::where('uid_lead', $si)->first();
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'reset-si-gp',
                    'vat' => 0,
                    'tax' => 0,
                    'ref_id' => $si,
                    'ref_number' => $orderDel->gp_submit_number,
                    'company_id' => auth()->user()->company_id
                ]);

                OrderSubmitLogDetail::updateOrCreate([
                    'order_submit_log_id' => $orderDel->id,
                    'order_id' => $orderDel->id
                ], [
                    'order_submit_log_id' => $orderDel->id,
                    'order_id' => $orderDel->id,
                    'status' => 'success',
                    'error_message' => 'Success reset si gp'
                ]);
                return response()->json(['status' => 'success', 'data' => ['invoice_number' => $lists]]);
            } else {
                $list_errors[] = $si;
                $orderDel = OrderDelivery::where('uid_lead', $si)->first();
                $orderSi = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'reset-si-gp',
                    'vat' => 0,
                    'tax' => 0,
                    'ref_id' => $si,
                    'ref_number' => $orderDel->gp_submit_number,
                    'company_id' => auth()->user()->company_id
                ]);

                OrderSubmitLogDetail::updateOrCreate([
                    'order_submit_log_id' => $orderDel->id,
                    'order_id' => $orderDel->id
                ], [
                    'order_submit_log_id' => $orderDel->id,
                    'order_id' => $orderDel->id,
                    'status' => 'failed',
                    'error_message' => 'Failed reset si gp'
                ]);

                return response()->json(['status' => 'error', 'data' => ['invoice_number' => $list_errors]]);
            }
        }
    }

    protected function generateHmacHeader($method, $path, $body = '')
    {
        $username = 'JgMP9NMoBLtdr5Wh';
        $secret = 'g41RMUBzJ670LJ2sQazBOEnbuprRAsyy';

        // Set timezone ke UTC
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $dateString = $date->format(\DateTime::RFC2822);

        $bodyDigest = '';
        if (!empty($body)) {
            $bodyHash = hash('sha256', $body, true);
            $bodyDigest = 'SHA-256=' . base64_encode($bodyHash);
        }

        $requestLine = $method . ' ' . $path . ' HTTP/1.1';
        $payload = implode("\n", ['date: ' . $dateString, $requestLine]);
        $digest = hash_hmac('sha256', $payload, $secret, true);
        $signature = base64_encode($digest);

        $header = 'hmac username="' . $username . '", algorithm="hmac-sha256", headers="date request-line", signature="' . $signature . '"';

        $idempotencyKey = Uuid::uuid4()->toString();

        return [
            'Date' => $dateString,
            'Authorization' => $header,
            'Digest' => $bodyDigest,
            'X-Idempotency-Key' => $idempotencyKey,
            'Content-Type' => 'application/json',
        ];
    }

    private function generateInvoiceNo($type)
    {
        if ($type == 'order-lead') {
            $username = auth()->user()->username ?? 99;
            $year = date('Y');
            $nomor = 'SI/' . $year . '/2' . $username . '000001';
            $rw = OrderLead::whereNotNull('invoice_number')->whereType('manual')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first();

            if ($rw) {
                $awal = substr($rw->invoice_number, -6);
                $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
                $nomor = 'SI/' . $year . '/2' . $next;
            }

            return $nomor;
        }

        if ($type == 'freebies') {
            $username = auth()->user()->username ?? 99;
            $year = date('Y');
            $nomor = 'SI/' . $year . '/2' . $username . '000001';
            $rw = OrderManual::whereNotNull('invoice_number')->whereType('freebies')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first();

            if ($rw) {
                $awal = substr($rw->invoice_number, -6);
                $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
                $nomor = 'SI/' . $year . '/2' . $next;
            }

            return $nomor;
        }

        $username = auth()->user()->username ?? 99;
        $year = date('Y');
        $nomor = 'SI/' . $year . '/2' . $username . '000001';
        $rw = OrderManual::whereNotNull('invoice_number')->whereType('manual')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first();

        if ($rw) {
            $awal = substr($rw->invoice_number, -6);
            $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
            $nomor = 'SI/' . $year . '/2' . $next;
        }

        return $nomor;
    }
}
