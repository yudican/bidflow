<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateLogQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        try {
            $response = $client->request('POST', 'https://crm-log.aimi.dev/api/create/log', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($this->data)
            ]);

            setSetting('success_create_log', 'Success');
            return true;
        } catch (\Throwable $th) {
            setSetting('error_create_log', 'Success - ' . $th->getMessage());

            return false;
        }
    }
}
