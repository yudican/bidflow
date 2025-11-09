<?php

namespace App\Jobs;

use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\OrderSubmitLogDetail;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitSIGpQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $type;
    protected $order_log_id;
    protected $body = [];
    protected $ids = [];
    protected $products = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, $order_log_id, $body = [], $ids = [], $products = [])
    {
        $this->type = $type;
        $this->order_log_id = $order_log_id;
        $this->body = $body;
        $this->ids = $ids;
        $this->products = $products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client(['verify' => false]);

        $ids = $this->ids;
        $type = $this->type;
        $order_log_id = $this->order_log_id;
        $body = $this->body;
        $products = $this->products;

        $orders = null;

        switch ($type) {
            case 'order-lead':
                $orders = OrderLead::query()->whereIn('uid_lead', $ids);
                break;
            case 'order-manual':
                $orders = OrderManual::query()->whereIn('uid_lead', $ids)->where('type', 'manual');
                break;

            default:
                $orders = OrderManual::query()->whereIn('uid_lead', $ids)->where('type', 'freebies');
                break;
        }
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
                    'Authorization: Bearer ' . getSetting('GP_TOKEN_2')
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
            }

            setSetting('GP_RESPONSE_' . $order_log_id, json_encode($responseJSON));
            if (isset($responseJSON['code'])) {
                if (in_array($responseJSON['code'], [200, 201])) {
                    foreach ($orders->get() as $key => $order) {
                        $order->update(['status_submit' => 'submited']);
                        foreach ($order->orderDelivery as $key => $product) {
                            if ($product->is_invoice == 1) {
                                foreach ($products as $key => $product_value) {
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
