<?php

namespace App\Console\Commands;

use App\Models\LogError;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GpRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gp:refreshtoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gp Refresh Token';

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
        $now = strtotime(date('Y-m-d H:i:s'));
        $token_expire = strtotime(getSetting('GP_TOKEN_EXPIRED_' . $code));
        $refresh_token = getSetting('GP_REFRESH_TOKEN_' . $code);

        setSetting('RUN_REFRESH_TOKEN_HARI', date('l, d M Y H:i:s'));
        setSetting('RUN_REFRESH_EXPIRE', $token_expire);
        if ($now > $token_expire) {
            $client = new Client();
            try {
                $response = $client->request('PUT', getSetting('GP_URL') . '/Token/refresh', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'ArthaKey' => getSetting('GP_ARTAKEY'),
                        'Authorization' => 'Bearer ' . $refresh_token
                    ],
                ]);

                $responseJSON = json_decode($response->getBody(), true);
                if (isset($responseJSON['code'])) {
                    if (in_array($responseJSON['code'], [200, 201])) {
                        setSetting('GP_TOKEN_' . $code, $responseJSON['data']['token']['access_token']);
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
}
