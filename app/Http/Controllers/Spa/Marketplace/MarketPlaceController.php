<?php

namespace App\Http\Controllers\Spa\Marketplace;

use App\Http\Controllers\Controller;
use App\Imports\Marketplace\MarketPlaceImportOrder;
use App\Jobs\CreateLogQueue;
use App\Models\CompanyAccount;
use App\Models\MPOrderList;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantBundlingStock;
use App\Models\ProductVariantStock;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\SkuMaster;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MarketPlaceController extends Controller
{
    public function index($order_id = null)
    {
        return view('spa.spa-index');
    }

    public function listOrder(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $shipping_status = $request->shipping_status;
        $warehouse = $request->warehouse;
        $channel = $request->channel;
        $store = $request->store;
        $customer_code = $request->customer_code;

        $order =  MPOrderList::query()->with('items');
        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('trx_id', 'like', "%$search%");
                $query->orWhere('customer_code', 'like', "%$search%");
                $query->orWhere('customer_name', 'like', "%$search%");
                $query->orWhere('channel', 'like', "%$search%");
                $query->orWhere('store', 'like', "%$search%");
                $query->orWhere('amount', 'like', "%$search%");
                $query->orWhere('shipping_fee', 'like', "%$search%");
                $query->orWhere('payment_method', 'like', "%$search%");
                $query->orWhere('warehouse', 'like', "%$search%");
                $query->orWhere('mp_fee', 'like', "%$search%");
                $query->orWhere('discount', 'like', "%$search%");
                $query->orWhere('trx_date', 'like', "%$search%");
                $query->orWhere('courir', 'like', "%$search%");
                $query->orWhere('awb', 'like', "%$search%");
                $query->orWhere('status', 'like', "%$search%");
                $query->orWhere('shipping_status', 'like', "%$search%");
            });
        }

        if ($status) {
            $order->whereIn('status', $status);
        }

        if ($shipping_status) {
            $order->whereIn('shipping_status', $shipping_status);
        }

        if ($warehouse) {
            $order->whereIn('warehouse', $warehouse);
        }

        if ($channel) {
            $order->whereIn('channel', $channel);
        }

        if ($store) {
            $order->whereIn('store', $store);
        }

        if ($customer_code) {
            $order->whereIn('customer_code', $customer_code);
        }


        $orders = $order->orderBy('trx_date', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $orders,
            'message' => 'List Order'
        ]);
    }

    function listOrderDetail($order_id)
    {
        $order = MPOrderList::with('items')->where('trx_id', str_replace('_', '/', $order_id))->first();

        if ($order) {
            return response()->json([
                'data' =>  $order,
                'message' => 'Success Get Order'
            ]);
        }

        return response()->json([
            'data' =>  null,
            'message' => 'Error Get Order, Order Not Found'
        ], 404);
    }

    function importOrder(Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('attachment');

            Excel::import(new MarketPlaceImportOrder(), $file);

            return response()->json(['message' => 'Data berhasil diimpor'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()], 500);
        }
    }

    public function submitMarketPlaceEthix(Request $request)
    {
        $orders = MPOrderList::whereIn('id', $request->ids)->get();
        $skuList = SkuMaster::whereStatus(1)->get();
        $productItems  = [];

        foreach ($orders as $order) {
            if ($order->status_ethix == 'submited') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order ' .  $order->trx_id . ' Telah Disubmit'
                ], 400);
            }
            $orderSi = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => 'submit-ethix',
                'vat' => 0,
                'tax' => 0,
                'ref_id' => $order->id
            ]);

            $order_log_id = $orderSi->id;

            foreach ($order->items as $value) {
                $sku = $value->sku;

                foreach ($skuList as $index => $itemSku) {
                    $skus = explode('-', $sku);
                    if (isset($skus) && count($skus) > 1) {
                        if (str_contains($itemSku->sku, $skus[0])) {
                            $sku = $itemSku->sku;
                        }
                    } else {
                        if (str_contains($itemSku->sku, $value->sku)) {
                            $sku = $itemSku->sku;
                        }
                    }
                }
                $productItems[] = [
                    "product_code" => $sku,
                    "product_name" => $value->product_name,
                    "quantity" => $value->qty,
                    "unit_price" => $value->final_price,
                    "weight" => 1
                ];
            }
            // Send To Ethix
            try {
                $company = CompanyAccount::find(1, ['account_code']);
                $headers = [
                    'secretcode: ' . getSetting('ETHIX_SECRETCODE_' . $company->account_code),
                    'secretkey: ' . getSetting('ETHIX_SECRETKEY_' . $company->account_code),
                    'Content-Type: application/json'
                ];

                $curl_post_data = array(
                    "client_code" => getSetting('ETHIX_CLIENTCODE_' . $company->account_code),
                    "location_code" => $order->warehouse_ethix,
                    "courier_name" => "ANTER AJA",
                    "delivery_type" => "REGULER",
                    "order_type" => "DLO",
                    "order_date" => "2023-08-23",
                    "order_code" => $order->trx_id,
                    "channel_origin_name" => "FIS",
                    "payment_date" => $order->trx_date,
                    "is_cashless" => true,
                    "recipient_name" => $order->customer_name,
                    "recipient_phone" => "08888888",
                    "recipient_subdistrict" => "Ciputat",
                    "recipient_district" => "Cipayung",
                    "recipient_city" => "Tangsel",
                    "recipient_province" => "Banten",
                    "recipient_country" => "Indonesia",
                    "recipient_address" => "jalan darat",
                    "recipient_postal_code" => "12270",
                    "Agent" => "Vidi",
                    "product_price" => $order->amount,
                    "product_discount" => $order->discount,
                    "shipping_discount" => "0",
                    "insurance_price" => "",
                    "total_price" => $order->amount,
                    "total_weight" => "1",
                    "total_koli" => "1",
                    "cod_price" => "0",
                    "shipping_price" => $order->shipping_fee,
                    "insurance_price" => "0",
                    "created_via" => "FIS System",
                    "product_information" => $productItems,
                );

                setSetting('so_manual_ethix_body', json_encode($curl_post_data));
                setSetting('ethix_body_headers', json_encode($headers));

                $url = 'https://wms.ethix.id/index.php?r=Externalv2/Order/PostOrder';
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $url);
                curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_TIMEOUT, 9000);
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
                $responseData = curl_exec($handle);
                curl_close($handle);

                setSetting('ethix_so_maeketplace', json_encode($responseData));
                $responseJSON = json_decode($responseData, true);
                // check is string
                if (!$responseJSON && is_string($responseData)) {
                    //    error submit ethix 1
                }

                // Check if any error occured
                if (curl_errno($handle)) {
                    //    error submit ethix 2
                }


                if (isset($responseJSON['status'])) {
                    if (in_array($responseJSON['status'], [400])) {
                        // success submit && load stock
                        foreach ($order->items as $value) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $value->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $value->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['message'],
                            ]);
                        }
                    }
                    if (in_array($responseJSON['status'], [200, 201])) {
                        // success submit && load stock
                        $order->update(['status_ethix' => 'submited']);
                        $warehouse = Warehouse::where('wh_id', $order->warehouse)->first(['id']);
                        if ($warehouse) {
                            foreach ($order->items as $value) {
                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $value->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $value->id,
                                    'status' => 'success',
                                    'error_message' => 0,
                                ]);
                            }
                            foreach ($productItems as $value) {
                                $this->updateStock($value, $warehouse->id);
                            }
                        }
                    }
                }
            } catch (\Throwable $th) {
                //throw $th;
                setSetting('ethix_so_maeketplace_error', $th->getMessage());
            }

            $productItems  = [];
        }
    }

    public function updateStock1($trans, $warehouse_id)
    {
        $product = ProductVariant::whereSku($trans['product_code'])->first();
        if ($product) {
            try {
                DB::beginTransaction();
                $product_variants = ProductVariant::where('product_id', $product->product_id)->get();
                $product = ProductVariant::find($trans->product_id);

                $product_master = Product::find($product->product_id);
                $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
                foreach ($product_variants as $key => $variant) {
                    $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                    $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                    $qty = $bundling_qty * $trans['quantity'];
                    foreach ($variant_stocks as $key => $stock) {
                        $stok = $stock->qty;
                        $temp = $stok - $qty;
                        $temp = $temp < 0 ? 0 : $temp;
                        $stock_of_market = $stock->stock_of_market - $qty;
                        $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
                        if ($temp >= 0) {
                            $stock->update(['qty' => $temp, 'stock_of_market' => floor($temp / $bundling_qty)]);
                        } else {
                            $stock->update(['qty' => 0, 'stock_of_market' => 0]);
                            $qty = $qty - $stok;
                        }
                    }
                }

                // update stock bundling
                $bundlings = ProductVariantBundling::where('product_variant_id', $product->id)->get();
                foreach ($bundlings as $key => $bundling) {
                    $bundling_stocks = ProductVariantBundlingStock::where('product_variant_bundling_id', $bundling->id)->where('warehouse_id', $warehouse_id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                    $qty = $bundling->product_qty * $trans['quantity'];
                    foreach ($bundling_stocks as $key => $stock) {
                        $stok = $stock->qty;
                        $temp = $stok - $qty;
                        $temp = $temp < 0 ? 0 : $temp;
                        $stock_off_market = $stock->stock_off_market - $trans['quantity'];
                        $stock_off_market = $stock_off_market < 0 ? 0 : $stock_off_market;
                        if ($temp >= 0) {
                            $stock->update(['qty' => $temp, 'stock_off_market' => $stock_off_market]);
                        } else {
                            $stock->update(['qty' => 0, 'stock_off_market' => 0]);
                            $qty = $qty - $stok;
                        }
                    }
                }


                $qty_master = $trans['quantity'] * $product->qty_bundling;
                saveLogStock([
                    'product_id' => $product->product_id,
                    'product_variant_id' => null,
                    'warehouse_id' => $warehouse_id,
                    'type_product' => 'master',
                    'type_stock' => 'out',
                    'type_transaction' => 'ethix',
                    'type_history' => 'so',
                    'name' => 'Transaction product product',
                    'qty' => $qty_master,
                    'description' => 'Transaction Product SO Ethix - ' . $trans->uid_lead,
                ]);


                $data_stock_1 = [
                    // 'uid_inventory'  => $uid_inventory,
                    'warehouse_id'  => $warehouse_id,
                    'product_id'  => $product->product_id,
                    'stock'  => $master_stock - $qty_master,
                    'ref' => "ethix - $trans->uid_lead",
                    'company_id' => 1,
                    'is_allocated' => 1,
                ];
                ProductStock::updateOrCreate([
                    'warehouse_id'  => $warehouse_id,
                    'product_id'  => $product->product_id,
                ], $data_stock_1);



                $dataLog = [
                    'log_type' => '[fis-dev]order_ethix',
                    'log_description' => 'Update Stock Order ethix - ' . $product->id,
                    'log_user' => auth()->user()->name,
                ];
                CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
            }
        } else {
            DB::rollback();
        }
    }

    public function updateStock($trans, $warehouse_id)
    {
        $product = ProductVariant::whereSku($trans['product_code'])->first();
        if ($product) {
            try {
                DB::beginTransaction();
                $company_id = 1;
                if ($product->is_bundling > 0) {
                    $bundlings = ProductVariantBundling::where('product_variant_id', $trans->product_id)->get();
                    foreach ($bundlings as $key => $bundling) {
                        $product_variants = ProductVariant::where('product_id', $bundling->product_id)->get();
                        $product_master = Product::find($bundling->product_id);
                        $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
                        foreach ($product_variants as $key => $variant) {
                            $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('company_id', $company_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                            $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                            $qty = $bundling_qty * $trans['quantity'];
                            foreach ($variant_stocks as $key => $stock) {
                                $stok = $stock->qty;
                                $temp = $stok - $qty;
                                $temp = $temp < 0 ? 0 : $temp;
                                $stock_of_market = $stock->stock_of_market - $qty;
                                $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
                                if ($temp >= 0) {
                                    $stock->update(['qty' => $temp, 'stock_of_market' => floor($temp / $bundling_qty)]);
                                } else {
                                    $stock->update(['qty' => 0, 'stock_of_market' => 0]);
                                    $qty = $qty - $stok;
                                }
                            }

                            saveLogStock([
                                'product_id' => $bundling->product_id,
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $warehouse_id,
                                'type_product' => 'variant',
                                'type_stock' => 'out',
                                'type_transaction' => 'manual',
                                'type_history' => 'so',
                                'name' => 'Transaction product product',
                                'qty' => $qty,
                                'description' => 'Transaction Product Ethix - ' . $trans->uid_lead,
                            ]);
                        }

                        $qty_master = $trans['quantity'] * $product->qty_bundling;
                        saveLogStock([
                            'product_id' => $bundling->product_id,
                            'product_variant_id' => null,
                            'warehouse_id' => $warehouse_id,
                            'type_product' => 'master',
                            'type_stock' => 'out',
                            'type_transaction' => 'manual',
                            'type_history' => 'so',
                            'name' => 'Transaction product product',
                            'qty' => $qty_master,
                            'description' => 'Transaction Product Ethix - ' . $trans->uid_lead,
                        ]);

                        $data_stock_1 = [
                            // 'uid_inventory'  => $uid_inventory,
                            'warehouse_id'  => $warehouse_id,
                            'product_id'  => $bundling->product_id,
                            'stock'  => $master_stock - $qty_master,
                            'ref' => "manual - $trans->uid_lead",
                            'company_id' => $company_id,
                            'is_allocated' => 1,
                        ];
                        ProductStock::updateOrCreate([
                            'warehouse_id'  => $warehouse_id,
                            'product_id'  => $bundling->product_id,
                            'company_id' => $company_id,
                        ], $data_stock_1);
                    }
                } else {
                    $product_variants = ProductVariant::where('product_id', $product->product_id)->get();
                    $product_master = Product::find($product->product_id);
                    $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
                    foreach ($product_variants as $key => $variant) {
                        $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('company_id', $company_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                        $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                        $qty = $bundling_qty * $trans['quantity'];
                        foreach ($variant_stocks as $key => $stock) {
                            $stok = $stock->qty;
                            $temp = $stok - $qty;
                            $temp = $temp < 0 ? 0 : $temp;
                            $stock_of_market = $stock->stock_of_market - $qty;
                            $stock_of_market = $stock_of_market < 0 ? 0 : $stock_of_market;
                            if ($temp >= 0) {
                                $stock->update(['qty' => $temp, 'stock_of_market' => floor($temp / $bundling_qty)]);
                            } else {
                                $stock->update(['qty' => 0, 'stock_of_market' => 0]);
                                $qty = $qty - $stok;
                            }
                        }

                        saveLogStock([
                            'product_id' => $product->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $warehouse_id,
                            'type_product' => 'variant',
                            'type_stock' => 'out',
                            'type_transaction' => 'manual',
                            'type_history' => 'so',
                            'name' => 'Transaction product product',
                            'qty' => $qty,
                            'description' => 'Transaction Product Ethix - ' . $trans->uid_lead,
                        ]);
                    }

                    $qty_master = $trans['quantity'] * $product->qty_bundling;
                    saveLogStock([
                        'product_id' => $product->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $warehouse_id,
                        'type_product' => 'master',
                        'type_stock' => 'out',
                        'type_transaction' => 'manual',
                        'type_history' => 'so',
                        'name' => 'Transaction product product',
                        'qty' => $qty_master,
                        'description' => 'Transaction Product Ethix - ' . $trans->uid_lead,
                    ]);

                    $data_stock_1 = [
                        // 'uid_inventory'  => $uid_inventory,
                        'warehouse_id'  => $warehouse_id,
                        'product_id'  => $product->product_id,
                        'stock'  => $master_stock - $qty_master,
                        'ref' => "manual - $trans->uid_lead",
                        'company_id' => $company_id,
                        'is_allocated' => 1,
                    ];

                    ProductStock::updateOrCreate([
                        'warehouse_id'  => $warehouse_id,
                        'product_id'  => $product->product_id,
                        'company_id' => $company_id,
                    ], $data_stock_1);
                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                setSetting("UPDATE_STOCK_ORDER_MANUAL_ERROR_{$trans->id}_{$warehouse_id}", $th->getMessage());
            }
        }
    }

    function updateWarehouse(Request $request)
    {
        $order = MPOrderList::where('trx_id', $request->orderId)->first();
        if ($order) {
            $order->update(['warehouse' => $request->wh_id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Diupdate'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal Diupdate'
        ], 400);
    }
}
