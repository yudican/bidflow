<?php

namespace App\Jobs;

use App\Models\LogError;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RequestNewAwbNumber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $transaction_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $transaction_id = $this->transaction_id;
        try {
            $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . "/shipment/v1/orders/$transaction_id/generate", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('POPAKET_TOKEN')
                ],
                'body' => json_encode(['client_order_no' => $transaction_id])
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status'])) {
                if ($responseJSON['status'] == 'success') {
                    GetOrderResi::dispatch($transaction_id)->onQueue('queue-log');
                }
            }
        } catch (Throwable $th) {
            LogError::updateOrCreate(['id' => null], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Awb Number Regenerate (RequestNewAwbNumber)',
            ]);
        }
    }
}
