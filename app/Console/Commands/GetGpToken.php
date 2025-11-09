<?php

namespace App\Console\Commands;

use App\Models\LogError;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GetGpToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gp:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get GP Token';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->getTokenGp('001');
        $this->getTokenGp('002');
        return Command::SUCCESS;
    }

    function getTokenGp($code)
    {
        $client = new Client();
        setSetting('GP_TOKEN_CHECK', 'OK');
        try {
            $response = $client->request('POST', getSetting('GP_URL') . '/Token/access', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'ArthaKey' => getSetting('GP_ARTAKEY'),
                ],
                'body' => json_encode([
                    'user' => getSetting('GP_USERNAME_' . $code),
                    'password' => getSetting('GP_PASSWORD'),
                ])
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['code'])) {
                setSetting('GP_TOKEN2', $responseJSON['code']);
                if ($responseJSON['code'] == 201) {
                    setSetting('GP_TOKEN', $responseJSON['data']['token']['access_token']);
                    setSetting('GP_TOKEN_2', $responseJSON['data']['token']['access_token']);
                    setSetting('GP_REFRESH_TOKEN_' . $code, $responseJSON['data']['token']['refresh_token']);
                    setSetting('GP_TOKEN_EXPIRED_' . $code, $responseJSON['data']['token']['access_token_expires_in']);
                    setSetting('GP_TOKEN_' . $code, $responseJSON['data']['token']['access_token']);
                }
            }
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Token GP',
            ]);
        }
    }
}
