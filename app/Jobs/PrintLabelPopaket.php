<?php

namespace App\Jobs;

use App\Models\LogError;
use App\Models\TransactionLabel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Livewire\ComponentConcerns\ReceivesEvents;

class PrintLabelPopaket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ReceivesEvents;
    protected $resi;
    protected $transaction_id;
    protected $print;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($resi, $id_transaksi, $print = false)
    {
        $this->resi = $resi;
        $this->transaction_id = $id_transaksi;
        $this->print = $print;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $resi = $this->resi;
        $id_transaksi = $this->transaction_id;
        $print = $this->print;
        try {
            $response = $client->request('GET', getSetting('POPAKET_BASE_URL') . '/shipment/v1/orders/' . $resi . '/label', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('POPAKET_TOKEN')
                ],
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            setSetting('response_label', json_encode($responseJSON));
            if (isset($responseJSON['status']) && $responseJSON['status'] == 'success') {
                $lable_url = $responseJSON['data']['url_label_pdf'];

                if ($print) {
                    // return response()->streamDownload(function () use ($lable_url) {
                    //     return file_get_contents($lable_url);
                    // }, 'LABEL-' . $id_transaksi . '.pdf');
                    // return $this->emit('printInvoice', $lable_url);
                } else {
                    TransactionLabel::create([
                        'id_transaksi' => $id_transaksi,
                        'label_url' => $lable_url,
                    ]);
                    LogError::updateOrCreate(['id' => 1], [
                        'message' => 'Get Label Success',
                        'trace' => json_encode($responseJSON),
                        'action' => 'Get Label ' . $resi . ' Queue (getLabel)',
                    ]);
                }
            }
        } catch (ClientException $th) {
            setSetting('response_label', json_encode($th->getTraceAsString()));
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get Label (getAwbNumber)',
            ]);
        }
    }
}
