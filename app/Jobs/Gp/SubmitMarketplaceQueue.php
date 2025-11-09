<?php

namespace App\Jobs\Gp;

use App\Events\ProgressSubmitMPEvent;
use App\Models\MPOrderList;
use App\Models\OrderSubmitLogDetail;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class SubmitMarketplaceQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $body;
    protected $order_log_id;
    protected $ids;
    protected $products;
    public function __construct($body, $order_log_id, $ids = [], $products = [])
    {
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
        $ids = $this->ids;
        $products = $this->products;
        $order_log_id = $this->order_log_id;
        $body = $this->body;

        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher(
            'd1b03f4c9a2b2345784b',
            '01e47b438588f220b64a',
            '1715128',
            $options
        );

        $orders = MPOrderList::query()->whereIn('id', $ids);
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
                setSetting('GP_RESPONSE_SUBMIT_MP_ERROR_' . $order_log_id, $response);
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
                $currentSuccess = getSetting('SUBMIT_PROGRESS_MP');
                $total = $currentSuccess + 1;
                $finalTotal = $total >= count($ids) ? count($ids) : $total;
                setSetting('SUBMIT_PROGRESS_MP', $total);
                $pusher->trigger('aimigroup-crm-development', 'progress-submit-mp', [
                    'progress' => $currentSuccess,
                    'total' => $finalTotal,
                    'percentage' => getPercentage($finalTotal, $currentSuccess)
                ]);
                return;
            }

            // Check if any error occured
            if (curl_errno($curl)) {
                setSetting('GP_RESPONSE_SUBMIT_MP_ERROR_' . $order_log_id, curl_error($curl));
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
                $currentSuccess = getSetting('SUBMIT_PROGRESS_MP');
                $total = $currentSuccess + 1;
                $finalTotal = $total >= count($ids) ? count($ids) : $total;
                setSetting('SUBMIT_PROGRESS_MP', $total);
                $pusher->trigger('aimigroup-crm-development', 'progress-submit-mp', [
                    'progress' => $currentSuccess,
                    'total' => $finalTotal,
                    'percentage' => getPercentage($finalTotal, $currentSuccess)
                ]);


                return;
            }

            setSetting('GP_RESPONSE_SUBMIT_MP_' . $order_log_id, json_encode($responseJSON));
            if (isset($responseJSON['code'])) {
                if (in_array($responseJSON['code'], [200, 201])) {
                    $currentSuccess = getSetting('SUBMIT_PROGRESS_MP');
                    $total = $currentSuccess + 1;
                    $finalTotal = $total >= count($ids) ? count($ids) : $total;
                    setSetting('SUBMIT_PROGRESS_MP', $total);
                    $pusher->trigger('aimigroup-crm-development', 'progress-submit-mp', [
                        'progress' => $currentSuccess,
                        'total' => $finalTotal,
                        'percentage' => getPercentage($finalTotal, $currentSuccess)
                    ]);


                    foreach ($orders->get() as $key => $order) {
                        $order->update(['status_gp' => 'submited']);
                        foreach ($order->items as $key => $product) {
                            foreach ($products as $key => $product_value) {
                                if ($product_value['id'] == $product->id) {
                                    $product->update(['gp_number' => getNumberGP($responseJSON['data'][0]['success'])]);
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
                $currentSuccess = getSetting('SUBMIT_PROGRESS_MP');
                $total = $currentSuccess + 1;
                $finalTotal = $total >= count($ids) ? count($ids) : $total;
                setSetting('SUBMIT_PROGRESS_MP', $total);
                $pusher->trigger('aimigroup-crm-development', 'progress-submit-mp', [
                    'progress' => $currentSuccess,
                    'total' => $finalTotal,
                    'percentage' => getPercentage($finalTotal, $currentSuccess)
                ]);
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
            setSetting('GP_RESPONSE_SUBMIT_MP_ERROR_' . $order_log_id, $responseBodyAsString);
            $currentSuccess = getSetting('SUBMIT_PROGRESS_MP');
            $total = $currentSuccess + 1;
            $finalTotal = $total >= count($ids) ? count($ids) : $total;

            setSetting('SUBMIT_PROGRESS_MP', $total);
            $pusher->trigger('aimigroup-crm-development', 'progress-submit-mp', [
                'progress' => $currentSuccess,
                'total' => $finalTotal,
                'percentage' => getPercentage($finalTotal, $currentSuccess)
            ]);

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
