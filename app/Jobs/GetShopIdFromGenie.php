<?php

namespace App\Jobs;

use App\Models\OrderListByGenie;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetShopIdFromGenie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order_id;
    protected $shopId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id, $shopId)
    {
        $this->order_id = $order_id;
        $this->shopId = $shopId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $signature = base64_encode(hash_hmac('sha256', "POST$/openapi/shop/v1/get$", 'b3436f168a4402b7', true));
        $order_id = $this->order_id;
        try {
            $response = $client->request('POST', 'https://genie-sandbox.advai.net/openapi/shop/v1/get', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Advai-Country' => 'ID',
                    'Authorization' => 'd20254aee13cc156:' . $signature
                ],
                'body' => json_encode([
                    'shopId' => $this->shopId
                ]),
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if ($responseJSON['code'] == 'SUCCESS') {
                OrderListByGenie::find($order_id)->update(['store' => $responseJSON['data']['name']]);
            }
        } catch (ClientException $th) {
        }
    }
}
