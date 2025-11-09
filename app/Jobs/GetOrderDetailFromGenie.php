<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class GetOrderDetailFromGenie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $orderId;
    protected $success_total;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId, $success_total)
    {
        $this->orderId = $orderId;
        $this->success_total = $success_total;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $client = new Client();
        // $signature = base64_encode(hash_hmac('sha256', "POST$/openapi/order/v2/get$", getSetting('GINEE_SECRET_KEY'), true));
        // $success_total = getSetting('genie_order_list_success_total') ?? 0;
        // setSetting('genie_order_list_success_total', $success_total + 1);
        // try {
        //     $response = $client->request('POST', getSetting('GINEE_URL') . '/openapi/order/v2/get', [
        //         'headers' => [
        //             'Content-Type' => 'application/json',
        //             'X-Advai-Country' => 'ID',
        //             'Authorization' => getSetting('GINEE_ACCESS_KEY') . ':' . $signature
        //         ],
        //         'body' => json_encode([
        //             'orderId' => $this->orderId
        //         ]),
        //     ]);
        //     $responseJSON = json_decode($response->getBody(), true);
        //     if ($responseJSON['code'] == 'SUCCESS') {
        //         SaveOrderListFromGenie::dispatch($responseJSON['data'], $this->success_total)->onQueue('queue-log');
        //     }
        // } catch (ClientException $th) {
        // }
    }
}
