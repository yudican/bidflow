<?php

namespace App\Jobs;

use App\Models\LogError;
use App\Models\Logistic;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateToggleLogistic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $logistic_id;
    protected $status;
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($logistic_id, $status, $type = 'logistics')
    {
        $this->logistic_id = $logistic_id;
        $this->status = $status;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $logistic_id = $this->logistic_id;
        $type = $this->type;
        $status = $this->status == 0 ? 'DELETE' : 'PUT';
        $client = new Client();
        try {
            $response = $client->request($status, getSetting('LOGISTIC_URL') . "/everpro/client-dashboard/shipment/v1/{$type}/{$logistic_id}/active", [
                'headers' => [
                    'Authorization' => 'Bearer ' . getSetting('LOGISTIC_AUTH_TOKEN')
                ],
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                return true;
            }
            return true;
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Queue Toggle Update kuir popaket (updateKurir) UpdateToggleLogistic ',
            ]);
            return true;
        }
    }
}
