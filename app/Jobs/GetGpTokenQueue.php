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

class GetGpTokenQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $username;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($username = null)
    {
        $this->username = $username;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $username = $this->username;

        try {
            $response = $client->request('POST', getSetting('GP_URL') . '/Token/access', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'ArthaKey' => getSetting('GP_ARTAKEY'),
                ],
                'body' => json_encode([
                    'user' => $username,
                    'password' => getSetting('GP_PASSWORD'),
                ])
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['code'])) {
                if (in_array($responseJSON['code'], [200, 201])) {
                    if ($username == 'flm') {
                        setSetting('GP_TOKEN_2', $responseJSON['data']['token']['access_token']);
                    } else {
                        setSetting('GP_TOKEN', $responseJSON['data']['token']['access_token']);
                    }
                }
            }

            return 0;
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Token GP',
            ]);
        }
    }
}
