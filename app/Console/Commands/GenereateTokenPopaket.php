<?php

namespace App\Console\Commands;

use App\Models\LogError;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GenereateTokenPopaket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'popaket:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate token for popaket';

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
            $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . '/auth/v1/token', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'client_key' => getSetting('POPAKET_CLIENT_KEY'),
                    'client_secret' => getSetting('POPAKET_SECRET_KEY'),
                ])
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status'])) {
                if ($responseJSON['status'] == 'success') {
                    setSetting('POPAKET_TOKEN', $responseJSON['data']['token']);
                    setSetting('POPAKET_EXP_TOKEN', Carbon::now()->addSeconds($responseJSON['data']['expires'])->format('Y-m-d H:i:s'));
                    return $responseJSON['data']['token'];
                }
            }

            return 0;
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Token Po Paket (GenereateTokenPopaketCommand)',
            ]);
            return 0;
        }
    }
}
