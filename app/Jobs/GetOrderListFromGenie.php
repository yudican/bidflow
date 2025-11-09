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
use Pusher\Pusher;

class GetOrderListFromGenie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $more = true;
    public $cursor = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($more = true, $cursor = [])
    {
        $this->more = $more;
        $this->cursor = $cursor;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        return $this->getOrderList($this->more, $this->cursor);
    }

    public function getOrderList($more = true, $cursorData = [])
    {
        // $client = new Client();
        // $signature = base64_encode(hash_hmac('sha256', "POST$/openapi/order/v2/list-order$", getSetting('GINEE_SECRET_KEY'), true));
        // $options = array(
        //     'cluster' => 'ap1',
        //     'useTLS' => true
        // );
        // $pusher = new Pusher(
        //     'eafb4c1c4f906c90399e',
        //     '01d9b57c3818c1644cb0',
        //     '1472093',
        //     $options
        // );

        // $data['message'] = 'hello world';
        // try {
        //     $response = $client->request('POST', getSetting('GINEE_URL') . '/openapi/order/v2/list-order', [
        //         'headers' => [
        //             'Content-Type' => 'application/json',
        //             'X-Advai-Country' => 'ID',
        //             'Authorization' => getSetting('GINEE_ACCESS_KEY') . ':' . $signature
        //         ],
        //         'body' => json_encode([
        //             'channel_id' => null,
        //             'size' => 10,
        //             "createSince" => "2022-10-01T00:00:04Z",
        //             "createTo" => "2022-10-15T00:00:04Z",
        //             'nextCursor' => $cursorData,
        //         ]),
        //     ]);

        //     $responseJSON = json_decode($response->getBody(), true);
        //     if ($responseJSON['code'] == 'SUCCESS') {
        //         // $total_data = $responseJSON['data']['total'];
        //         $total_data = getSetting('genie_order_list_total') ?? 0;
        //         $success_total = getSetting('genie_order_list_success_total') ?? 0;

        //         if (count($responseJSON['data']['content']) > 0) {
        //             $cursor = [];
        //             setSetting('genie_order_list_total', count($responseJSON['data']['content']) + $total_data);
        //             foreach ($responseJSON['data']['content'] as $order) {
        //                 $cursor = $order['nextCursor'];
        //                 GetOrderDetailFromGenie::dispatch($order['orderId'], $success_total)->onQueue('queue-log');
        //             }

        //             $percentage = 0;
        //             if ($success_total > 0) {
        //                 $percentage = getPercentage($success_total, $total_data);
        //             }

        //             $pusher->trigger('aimi', 'progress', ['total' => $total_data, 'success' => $success_total, 'status' => 'sync', 'percentage' => $percentage]);
        //             if ($more) {
        //                 GetOrderListFromGenie::dispatch($responseJSON['data']['more'], $cursor)->onQueue('queue-log');
        //             }
        //         } else {
        //             $pusher->trigger('aimi', 'progress', ['total' => $total_data, 'success' => $success_total, 'status' => 'finish', 'percentage' => 100]);
        //             setSetting('sync', 'false');
        //             removeSetting('genie_order_list_total');
        //             removeSetting('genie_order_list_success_total');

        //             OrderListByGenie::where('status_sync', 1)->update(['status_sync' => 0]);
        //         }
        //     }
        // } catch (ClientException $th) {
        // }
    }
}
