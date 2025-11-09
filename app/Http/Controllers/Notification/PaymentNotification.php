<?php

namespace App\Http\Controllers\Notification;

use App\Events\PaymentSuccessEvent;
use App\Events\UpdateTransactionEvent;
use App\Http\Controllers\Controller;
use App\Jobs\CreateOrderPopaket;
use App\Models\InventoryItem;
use App\Models\LogError;
use App\Models\LogThirdPayment;
use App\Models\MasterPoint;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantStock;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Models\UserPoint;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentNotification extends Controller
{
    // midtrans payment notification
    public function notifications(Request $notif)
    {
        $transaction = $notif->transaction_status;
        $fraud = $notif->fraud_status;
        $transaction_data = Transaction::where('id_transaksi', $notif->order_id)->first();
        $transaction_data_agent = TransactionAgent::where('id_transaksi', $notif->order_id)->first();
        if ($transaction_data_agent) {
            $transaction_data = $transaction_data_agent;
        }
        $resi = null;
        if (in_array($transaction, ['capture', 'settlement'])) {
            if ($fraud == 'challenge') {
                try {
                    DB::beginTransaction();
                    $this->logPayment($notif, $transaction_data->id);
                    // TODO Set payment status in merchant's database to 'challenge'
                    if ($transaction_data) {
                        $transaction_data->update(['status' => 3]);
                        $notification_data = [
                            'name' => $transaction_data->user->name,
                            'rincian_bayar' => getRincianPembayaran($transaction_data),
                            'rincian_transaksi' => getRincianTransaksi($transaction_data),
                        ];

                        // send event payment
                        $paymentData = [
                            'transaction_id' => $transaction_data->id,
                            'email' => $transaction_data->user->email,
                        ];
                        event(new PaymentSuccessEvent($paymentData));
                        event(new UpdateTransactionEvent($paymentData));

                        TransactionStatus::create([
                            'id_transaksi' => $notif->order_id,
                            'status' => 3,
                        ]);
                        CreateOrderPopaket::dispatch($transaction_data)->onQueue('queue-log');
                        createNotification('TRS200', ['user_id' => $transaction_data->user->id, 'other_id' => $transaction_data->id], $notification_data, ['transaction_id' => $transaction_data->id]);

                        if ($transaction_data?->user?->device_id) {
                            createNotification('MCOV001', ['device_id' => $transaction_data?->user?->device_id]);
                        }

                        $masterPoint = MasterPoint::limit(1)->get();

                        foreach ($masterPoint as $point) {
                            if ($point->type == 'transaction') {
                                if ($transaction_data->nominal >= $point->min_trans && $transaction_data->nominal <= $point->max_trans) {
                                    UserPoint::create([
                                        'user_id' => $transaction_data->user_id,
                                        'point' => $point->point
                                    ]);
                                }
                            } else {
                                UserPoint::create([
                                    'user_id' => $transaction_data->user_id,
                                    'point' => $transaction_data->transactionDetail->count() * $point->point
                                ]);
                            }
                        }

                        foreach ($transaction_data->transactionDetail as $trans) {
                            if ($trans->product) {
                                // $trans->product()->update(['stock' => $trans->product->stock - $trans->qty]);

                                $this->updateStock($trans, $transaction_data->shipper_address_id);
                                // $stock = ProductStock::where('product_id', $trans->product_id);
                                // if ($trans->product_variant_id) {
                                //     $stock = ProductStock::where('product_variant_id', $trans->product_variant_id);
                                // }

                                // $product_stock = $stock->where('warehouse_id', $transaction_data->warehouse_id)->first();
                                // if ($product_stock) {
                                //     $product_stock->update(['stock' => $product_stock->stock - $trans->qty]);
                                // }
                            }
                        }
                    }
                    DB::commit();
                    $respon = [
                        'status' => true,
                        'status_code' => 200,
                        'data' => $resi
                    ];
                    return response()->json($respon, 200);
                } catch (\Throwable $th) {
                    DB::rollback();
                    $respon = [
                        'status' => true,
                        'status_code' => 400,
                        'message' => $th->getMessage(),
                    ];
                    LogError::updateOrCreate(['id' => 1], [
                        'message' => $th->getMessage(),
                        'trace' => $th->getTraceAsString(),
                        'action' => 'transactionNotificationaccept',
                    ]);
                    return response()->json($respon, 200);
                }
            } else if ($fraud == 'accept') {
                try {
                    DB::beginTransaction();
                    $this->logPayment($notif, $transaction_data->id);
                    if ($transaction_data) {
                        $transaction_data->update(['status' => 3]);
                        TransactionStatus::create([
                            'id_transaksi' => $notif->order_id,
                            'status' => 3,
                        ]);

                        // send event payment
                        $paymentData = [
                            'transaction_id' => $transaction_data->id,
                            'email' => $transaction_data->user->email,
                        ];
                        event(new PaymentSuccessEvent($paymentData));
                        event(new UpdateTransactionEvent($paymentData));

                        CreateOrderPopaket::dispatch($transaction_data)->onQueue('queue-log');
                        $notification_data = [
                            'name' => $transaction_data->user->name,
                            'rincian_bayar' => getRincianPembayaran($transaction_data),
                            'rincian_transaksi' => getRincianTransaksi($transaction_data),
                        ];
                        // $resi = $this->generateCode($transaction_data);
                        // $shipping->createShippingOrderValidateToken($transaction_data);
                        createNotification('TRS200', ['user_id' => $transaction_data->user->id, 'other_id' => $transaction_data->id], $notification_data, ['transaction_id' => $transaction_data->id]);
                        $masterPoint = MasterPoint::limit(1)->get();

                        foreach ($masterPoint as $point) {
                            if ($point->type == 'transaction') {
                                if ($transaction_data->nominal >= $point->min_trans && $transaction_data->nominal <= $point->max_trans) {
                                    UserPoint::create([
                                        'user_id' => $transaction_data->user_id,
                                        'point' => $point->point
                                    ]);
                                }
                            } else {
                                UserPoint::create([
                                    'user_id' => $transaction_data->user_id,
                                    'point' => $transaction_data->transactionDetail->count() * $point->point
                                ]);
                            }
                        }
                        foreach ($transaction_data->transactionDetail as $trans) {
                            if ($trans->product) {
                                $this->updateStock($trans, $transaction_data->shipper_address_id);
                                // $trans->product()->update(['stock' => $trans->product->stock - $trans->qty]);
                                // $stock = ProductStock::where('product_id', $trans->product_id);
                                // if ($trans->product_variant_id) {
                                //     $stock = ProductStock::where('product_variant_id', $trans->product_variant_id);
                                // }

                                // $product_stock = $stock->where('warehouse_id', $transaction_data->warehouse_id)->first();
                                // if ($product_stock) {
                                //     $product_stock->update(['stock' => $product_stock->stock - $trans->qty]);
                                // }
                            }
                        }

                        DB::commit();

                        $respon = [
                            'status' => true,
                            'status_code' => 200,
                            'data' => $resi
                        ];
                        return response()->json($respon, 200);
                    }
                } catch (\Throwable $th) {
                    DB::rollBack();
                    $respon = [
                        'status' => true,
                        'status_code' => 400,
                        'message' => $th->getMessage(),
                    ];
                    LogError::updateOrCreate(['id' => 1], [
                        'message' => $th->getMessage(),
                        'trace' => $th->getTraceAsString(),
                        'action' => 'transactionNotificationaccept',
                    ]);
                    return response()->json($respon, 200);
                }
            }
        } else if ($transaction == 'cancel') {
            if ($fraud == 'challenge') {
                $this->logPayment($notif, $transaction_data->id);
                // TODO Set payment status in merchant's database to 'failure'
                if ($transaction_data) {
                    $transaction_data->update(['status' => 4]);
                    TransactionStatus::create([
                        'id_transaksi' => $notif->order_id,
                        'status' => 4,
                    ]);
                }
            } else if ($fraud == 'accept') {
                $this->logPayment($notif, $transaction_data->id);
                // TODO Set payment status in merchant's database to 'failure'
                if ($transaction_data) {
                    $transaction_data->update(['status' => 4]);
                    TransactionStatus::create([
                        'id_transaksi' => $notif->order_id,
                        'status' => 4,
                    ]);
                }
            }
        } else if ($transaction == 'deny') {
            $this->logPayment($notif, $transaction_data->id);
            // TODO Set payment status in merchant's database to 'failure'
            if ($transaction_data) {
                createNotification('ORC400', ['user_id' => $transaction_data->user->id, 'other_id' => $transaction_data->id], ['brand' => $transaction_data->brand->name], ['transaction_id' => $transaction_data->id]);
                $transaction_data->update(['status' => 6]);
                TransactionStatus::create([
                    'id_transaksi' => $notif->order_id,
                    'status' => 6,
                ]);
            }
        } else if ($transaction == 'expire') {
            if ($fraud == 'challenge') {
                $this->logPayment($notif, $transaction_data->id);
                // TODO Set payment status in merchant's database to 'failure'
                if ($transaction_data) {
                    createNotification('ORC400', ['user_id' => $transaction_data->user->id, 'other_id' => $transaction_data->id], ['brand' => $transaction_data->brand->name], ['transaction_id' => $transaction_data->id]);
                    $transaction_data->update(['status' => 6]);
                    TransactionStatus::create([
                        'id_transaksi' => $notif->order_id,
                        'status' => 6,
                    ]);
                }
            } else if ($fraud == 'accept') {
                $this->logPayment($notif, $transaction_data->id);
                // TODO Set payment status in merchant's database to 'failure'
                if ($transaction_data) {
                    createNotification('ORC400', ['user_id' => $transaction_data->user->id, 'other_id' => $transaction_data->id], ['brand' => $transaction_data->brand->name], ['transaction_id' => $transaction_data->id]);
                    $transaction_data->update(['status' => 6]);
                    TransactionStatus::create([
                        'id_transaksi' => $notif->order_id,
                        'status' => 6,
                    ]);
                }
            }
        }
    }

    public function logPayment($response_payment, $transaction_id)
    {
        try {
            LogThirdPayment::create([
                'transaction_id' => $transaction_id,
                'third_transaction_id' => $response_payment->transaction_id,
                'third_transaction_status' => $response_payment->transaction_status,
                'third_transaction_message' => $response_payment->status_message,
                'third_transaction_payment_type' => $response_payment->payment_type,
                'third_transaction_gross_amount' => $response_payment->gross_amount,
                'third_transaction_fraud_status' => $response_payment->fraud_status,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    public function updateStock($trans, $warehouse_id, $qty_delivery = 0)
    {
        try {
            DB::beginTransaction();
            $company_id = 1;
            $product = ProductVariant::find($trans->product_id);
            if ($product->is_bundling > 0) {
                $bundlings = ProductVariantBundling::where('product_variant_id', $trans->product_id)->get();
                foreach ($bundlings as $key => $bundling) {
                    $product_variants = ProductVariant::where('product_id', $bundling->product_id)->get();
                    $product_master = Product::find($bundling->product_id);
                    $master_stock = (int) getStock($product_master->stock_warehouse, $warehouse_id);
                    foreach ($product_variants as $key => $variant) {
                        $variant_stocks = ProductVariantStock::where('warehouse_id', $warehouse_id)->where('company_id', $company_id)->where('product_variant_id', $variant->id)->where('qty', '>', 0)->orderBy('created_at', 'asc')->get();
                        $bundling_qty = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                        $qty_stock = $qty_delivery > 0 ? $qty_delivery : $trans->qty;
                        $qty = $bundling_qty * $qty_stock;
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
                            'description' => 'Transaction Product SO Manual - ',
                        ]);
                    }

                    $qty_master = $qty_stock * $product->qty_bundling;
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
                        'description' => 'Transaction Product SO Manual - ',
                    ]);

                    $data_stock_1 = [
                        // 'uid_inventory'  => $uid_inventory,
                        'warehouse_id'  => $warehouse_id,
                        'product_id'  => $bundling->product_id,
                        'stock'  => $master_stock - $qty_master,
                        'ref' => "manual - ",
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
                    $qty_stock = $qty_delivery > 0 ? $qty_delivery : $trans->qty;
                    $qty = $bundling_qty * $qty_stock;
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
                        'description' => 'Transaction Product SO Manual - ',
                    ]);
                }

                $qty_master = $qty_stock * $product->qty_bundling;
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
                    'description' => 'Transaction Product SO Manual - ',
                ]);

                $data_stock_1 = [
                    // 'uid_inventory'  => $uid_inventory,
                    'warehouse_id'  => $warehouse_id,
                    'product_id'  => $product->product_id,
                    'stock'  => $master_stock - $qty_master,
                    'ref' => "manual - ",
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
