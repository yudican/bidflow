<?php

namespace App\Jobs;

use App\Models\LogError;
use App\Models\Logistic;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CreateOrderPopaket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $transaction;
    protected $cod = false;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($transaction, $cod = false)
    {
        $this->transaction = $transaction;
        $this->cod = $cod;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $transaction = $this->transaction;
        setSetting('transaction', json_encode($transaction));
        $submitLog = OrderSubmitLog::create([
            'submited_by' => auth()->user()->id,
            'type_si' => 'order-popaket',
            'vat' => 0,
            'tax' => 0,
            'ref_id' => $transaction->id
        ]);
        if ($transaction) {
            $durations = explode('-', str_replace(' ', '-', $transaction?->shippingType?->shipping_duration ?? '1-2'));
            $min_duration = 1;
            $max_duration = 2;
            if (is_array($durations)) {
                $min_duration = isset($durations[0]) ? $durations[0] : 1;
                $max_duration = isset($durations[1]) ? $durations[1] : 2;
            }
            $products = [];
            foreach ($transaction->transactionDetail as $key => $product) {
                $products[] = $product->product->name;
            }

            $token = getSetting('POPAKET_TOKEN');
            $shipping_price = $transaction?->shippingType?->shipping_price ?? 0;
            $shipping_discount = $transaction?->shippingType?->shipping_discount ?? 0;
            $shipping_type_code = $transaction?->shippingType?->shipping_type_code ?? 0;

            $logistic = Logistic::whereHas('logisticRates', function ($query) use ($shipping_type_code) {
                $query->where('logistic_rate_code', $shipping_type_code);
            })->first(['logistic_shipping_type']);

            $shipment_type = $logistic ? $logistic->logistic_shipping_type : "DROP";
            $warehouse = $transaction->data_warehouse;
            $brand = $transaction->data_brand;
            $address = $transaction->data_user_address;
            $customer = $transaction->data_user;
            $data = [
                "client_order_no" => $transaction->id_transaksi,
                "cod_price" => 0,
                "height" => 10,
                "insurance_price" => 0,
                "is_cod" => false,
                "is_use_insurance" => false,
                "length" => 10,
                "max_duration" => intval($min_duration) > 0 ? intval($min_duration) : 1,
                "min_duration" => intval($max_duration) > 0 ? intval($min_duration) : 1,
                "package_price" => $shipping_price - $shipping_discount,
                "package_type_id" => 1,
                "rate_code" => $transaction->shippingType->shipping_type_code,
                "receiver_address" => $address ? $address['alamat_detail'] : '-',
                "receiver_address_note" => isset($address['catatan']) ? $address['catatan'] : $transaction->note,
                "receiver_email" => $customer ? $customer['email'] : '-',
                "receiver_name" => $customer ? $customer['name'] : '-',
                "receiver_phone" => $customer ? formatPhone($customer['telepon'], '08') : '-',
                "receiver_postal_code" => $transaction->shippingType->shipping_destination,
                "shipment_price" => intval($transaction->shippingType->shipping_price),
                "shipment_type" => $shipment_type,
                "shipper_address" => $warehouse ? $warehouse['alamat'] : '-',
                "shipper_email" => $brand ? $brand['brand_email'] : '-',
                "shipper_name" => $brand ? $brand['brand_name'] : '-',
                "shipper_phone" => $warehouse ? formatPhone($warehouse['telepon'], '08') : '-',
                "shipper_postal_code" => $transaction->shippingType->shipping_origin,
                "shipping_note" => 'Kode Produk 52',
                "weight" => $transaction->weight_total,
                "width" => 12
            ];

            if ($shipment_type == 'PICKUP') {
                $data['pickup_time'] = strtotime(Carbon::now()->addDay(1));
            }

            setSetting('popaket_payload', json_encode($data));

            // if (count($products) > 0) {
            //     $data['package_desc'] = implode(',', $products);
            // } else {
            //     $data['package_desc'] = 'Kardus';
            // }

            $data['package_desc'] = 'Kardus';

            try {
                DB::beginTransaction();
                $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . '/shipment/v1/orders', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                    ],
                    'body' => json_encode($data)
                ]);

                $responseJSON = json_decode($response->getBody(), true);
                setSetting('popaket_order', json_encode($responseJSON));
                if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $submitLog->id,
                        'order_id' => $transaction->id
                    ], [
                        'order_submit_log_id' => $submitLog->id,
                        'order_id' => $transaction->id,
                        'status' => 'success',
                        'error_message' => json_encode($responseJSON['data'])
                    ]);

                    $transaction->update(['awb_status' => 2]);
                    GetOrderResi::dispatch($transaction->id_transaksi)->onQueue('queue-api');
                } else {
                    try {
                        Transaction::find($transaction->id)->update(['status' => 3, 'status_delivery' => 0]);
                    } catch (\Throwable $th) {
                        setSetting('popaket_order_error_transaction_' . $transaction->id, $transaction->id);
                    }
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $submitLog->id,
                        'order_id' => $transaction->id
                    ], [
                        'order_submit_log_id' => $submitLog->id,
                        'order_id' => $transaction->id,
                        'status' => 'error',
                        'error_message' => json_encode($responseJSON['data'])
                    ]);
                }

                DB::commit();
            } catch (ClientException $th) {
                DB::rollBack();
                $response = $th->getResponse();
                setSetting('popaket_order_error', $response->getBody()->getContents());
                try {
                    Transaction::find($transaction->id)->update(['status' => 3, 'status_delivery' => 0]);
                } catch (\Throwable $th) {
                    setSetting('popaket_order_error_transaction_' . $transaction->id, $transaction->id);
                }
                OrderSubmitLogDetail::updateOrCreate([
                    'order_submit_log_id' => $submitLog->id,
                    'order_id' => $transaction->id
                ], [
                    'order_submit_log_id' => $submitLog->id,
                    'order_id' => $transaction->id,
                    'status' => 'error',
                    'error_message' => $response->getBody()->getContents()
                ]);
            }
        }
    }
}
