<?php

namespace App\Console\Commands;

use App\Models\LogError;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GenereateTokenPopaketLogistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logistic-popaket:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate token for logistic popaket';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new Client();

        try {
            $response = $client->request('POST', getSetting('LOGISTIC_URL') . '/api/auth/v2/login-email', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'device_id' => '6a237757-29fd-6c06-8d20-d383cbe27f9b',
                    'email' => 'vidi.aimigroup@gmail.com',
                    'password' => 'p4ssw0rd123',
                ])
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status'])) {
                if ($responseJSON['status'] == 'success') {
                    setSetting('LOGISTIC_AUTH_TOKEN', $responseJSON['data']['token']);
                }
            }

            return 0;
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 90], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Token Logistic Po Paket (GenereateTokenPopaketLogisticCommand)',
            ]);
            return 0;
        }
    }
}
