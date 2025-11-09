<?php

namespace App\Http\Controllers\Shipping;

use App\Http\Controllers\Controller;
use App\Jobs\GetOrderResi;
use App\Models\LogError;
use App\Models\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class PopaketController extends Controller
{
    public function generateNewToken()
    {
        $client = new Client();
        try {
            $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . '/auth/v1/token', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'client_key' => getSetting('POPAKET_CLIENT_KEY'),
                    'client_secret' => getSetting('POPAKET_SECRET_KEY'),
                ])
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status'])) {
                if ($responseJSON['status'] == 'success') {
                    setSetting('POPAKET_TOKEN', $responseJSON['data']['token']);
                    setSetting('POPAKET_EXP_TOKEN', Carbon::now()->addSeconds($responseJSON['data']['expires'])->format('Y-m-d H:i:s'));
                    return $responseJSON['data']['token'];
                }
            }

            return null;
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Token Po Paket (generateNewToken)',
            ]);
            return null;
        }
    }
    // validate token create order
    public function createShippingOrderValidateToken($transaction = null, $cod = false)
    {
        $now = date('Y-m-d H:i:s');
        $exp_date = strtotime(getSetting('POPAKET_EXP_TOKEN'));
        $now_date = strtotime($now);
        $token = getSetting('POPAKET_TOKEN');
        if ($token) {
            if ($now_date > $exp_date) {
                $newToken =  $this->generateNewToken();
                return $this->createShippingOrder($transaction, $newToken, $cod);
            } else {
                return $this->createShippingOrder($transaction, $token, $cod);
            }
        }

        $newToken =  $this->generateNewToken();
        return $this->createShippingOrder($transaction, $newToken, $cod);
    }

    // create shipping order
    public function createShippingOrder($transaction, $token, $cod = false)
    {
        $client = new Client();
        $durations = explode('-', str_replace(' ', '-', $transaction->shippingType->shipping_duration));
        $min_duration = 1;
        $max_duration = 2;
        if (is_array($durations)) {
            $min_duration = isset($durations[0]) ? $durations[0] : 1;
            $max_duration = isset($durations[1]) ? $durations[1] : 2;
        }

        $data = [
            "client_order_no" => $transaction->id_transaksi,
            "cod_price" => $cod ? $transaction->nominal : 0,
            "height" => 10,
            "insurance_price" => 0,
            "is_cod" => $cod,
            "is_use_insurance" => false,
            "length" => 10,
            "max_duration" => intval($min_duration),
            "min_duration" => intval($max_duration),
            "package_desc" => "Kardus",
            "package_price" => 0,
            "package_type_id" => 1,
            "rate_code" => $transaction->shippingType->shipping_type_code,
            "receiver_address" => $transaction->addressUser->alamat_detail,
            "receiver_address_note" => $transaction->addressUser->catatan ?? '',
            "receiver_email" => $transaction->user->email,
            "receiver_name" => $transaction->user->name,
            "receiver_phone" => $transaction->user->telepon,
            "receiver_postal_code" => $transaction->shippingType->shipping_destination,
            "shipment_price" => intval($transaction->shippingType->shipping_price),
            "shipment_type" => "DROP",
            "shipper_address" => $transaction->brand->alamat,
            "shipper_email" => $transaction->brand->email,
            "shipper_name" => $transaction->brand->name,
            "shipper_phone" => $transaction->brand->phone,
            "shipper_postal_code" => $transaction->shippingType->shipping_origin,
            "shipping_note" => "Hati hati",
            "weight" => 1,
            "width" => 12
        ];
        try {
            $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . '/shipment/v1/orders', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'body' => json_encode($data)
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                // $this->getAwbNumber($transaction, $token);
                LogError::updateOrCreate(['id' => 1], [
                    'message' => 'Succes Create Order',
                    'trace' => json_encode($responseJSON['data']),
                    'action' => 'Create Order Success',
                ]);
                // GetOrderResi::dispatch($transaction->id_transaksi)->onQueue('queue-log');
                return true;
            }
            return false;
        } catch (ClientException $th) {
            $response = $th->getResponse();
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $response->getBody()->getContents(),
                'action' => 'Create Order Po Paket (createShippingOrder)',
            ]);
            if ($th->getCode() == 401) {
                return $this->createShippingOrderValidateToken($transaction);
            }
            return true;
        }
    }

    public function getAwbNumber($transaction, $token)
    {
        if ($transaction) {
            $client = new Client();
            $transaction_id = $transaction->id_transaksi;
            try {
                $response = $client->request('GET', getSetting('POPAKET_BASE_URL') . "/shipment/v1/orders/{$transaction_id}/awb", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ],
                ]);
                $responseJSON = json_decode($response->getBody(), true);
                if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {

                    $transaction->update(['resi' => $responseJSON['data']['awb_number']]);
                }
                return false;
            } catch (\Throwable $th) {
                LogError::updateOrCreate(['id' => 1], [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                    'action' => 'Create Order Po Paket (createShippingOrder)',
                ]);
                return true;
            }
        }
    }
}
