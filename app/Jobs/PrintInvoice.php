<?php

namespace App\Jobs;

use App\Models\LogError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PrintInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data = [];
    protected $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $task)
    {
        $this->data = $data;
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $data = $this->data;
        try {
            $response = $client->request('POST', 'https://giraffe.daftar-agen.com/task', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'topic' => 'aimi_maintasks',
                    'task' => 'html_to_pdf',
                    'data' => [
                        'papersize' => 'A4',
                        'urls' => $data,
                    ],
                ]),
            ]);
            $responseJSON = json_decode($response->getBody(), true);
        } catch (ClientException $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Bulk Print',
            ]);
        }
    }
}
