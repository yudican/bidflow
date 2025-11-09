<?php

namespace App\Jobs;

use App\Models\GpSusmissionLogError;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GpSoSubmisionQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ginees = [];
    protected $headerGp = [];
    protected $detail = [];
    protected $list_order_gp_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ginees, $headerGp, $detail, $list_order_gp_id)
    {
        $this->ginees = $ginees;
        $this->headerGp = $headerGp;
        $this->detail = $detail;
        $this->list_order_gp_id = $list_order_gp_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $headerGp = [];
        // $headerGpTemp = [];
        // $detail = [];

        // foreach ($this->headerGp as $keys => $values) {
        //     foreach ($values as $key => $value) {
        //         $headerGpTemp[] = $value;
        //     }
        // }
        // $no = 1;
        // foreach ($headerGpTemp as $key => $value) {
        //     if (isset($headerGp[$value['CUSTNMBR']]['FREIGHT'])) {
        //         $headerGp[$value['CUSTNMBR']]['FREIGHT'] += $value['FREIGHT'];
        //     } else if (isset($headerGp[$value['CUSTNMBR']]['MISCAMNT'])) {
        //         $headerGp[$value['CUSTNMBR']]['MISCAMNT'] += $value['MISCAMNT'];
        //     } else if (isset($headerGp[$value['CUSTNMBR']]['TRDISAMT'])) {
        //         $headerGp[$value['CUSTNMBR']]['TRDISAMT'] += $value['TRDISAMT'];
        //     } else {
        //         $next = sprintf("%04d", ((int)$no));
        //         $so_number = 'SO/MP/' . date('Y') . '/' . date('m') . '/' . $next;
        //         $headerGp[$value['CUSTNMBR']] = $value;
        //         $headerGp[$value['CUSTNMBR']] = $so_number;
        //     }
        //     $no++;
        // }


        // foreach ($this->detail as $key => $value) {
        //     $detail[] = $value;
        // }

        // $this->submitGp($this->ginees, array_values($headerGp), $detail, $this->list_order_gp_id);
    }

    private function submitGp($ginees, $headerGp, $detail, $list_order_gp_id)
    {
        // $client = new Client();
        // try {
        //     $response = $client->request('POST', getSetting('GP_URL') . '/SO/SOEntry', [
        //         'headers' => [
        //             'Content-Type' => 'application/json',
        //             'ArthaKey' => getSetting('GP_ARTAKEY'),
        //             'Authorization' => 'Bearer ' . getSetting('GP_TOKEN'),
        //         ],
        //         'body' => json_encode([
        //             'header' => $headerGp,
        //             'line' => $detail
        //         ])
        //     ]);

        //     $responseJSON = json_decode($response->getBody(), true);
        //     if (isset($responseJSON['code'])) {
        //         if (in_array($responseJSON['code'], [200,201])) {
        //             foreach ($ginees as $key => $ginee) {
        //                 $ginee->update(['status_submit' => 'submited']);
        //             }

        //             return true;
        //         }
        //     }

        //     if (isset($responseJSON['desc'])) {
        //         foreach ($ginees as $key => $ginee) {
        //             GpSusmissionLogError::updateOrCreate(['id' => 1], [
        //                 'list_order_gp_id' => $list_order_gp_id,
        //                 'ginee_id' => $ginee->id,
        //                 'error_message' => $responseJSON['desc'],
        //                 'type' => 'gp'
        //             ]);
        //         }
        //     }
        //     return true;
        // } catch (\Throwable $th) {
        //     foreach ($ginees as $key => $ginee) {
        //         GpSusmissionLogError::updateOrCreate([
        //             'list_order_gp_id' => $list_order_gp_id,
        //             'ginee_id' => $ginee->id,
        //             'error_message' => "Submit GP Error : " . $th->getMessage(),
        //             'type' => 'gp'
        //         ]);
        //     }

        //     return false;
        // }
    }
}
