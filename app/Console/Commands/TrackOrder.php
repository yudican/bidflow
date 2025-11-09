<?php

namespace App\Console\Commands;

use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\Transaction;
use App\Models\TransactionDeliveryStatus;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TrackOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:track';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order Track';

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
        $resis = Transaction::whereIn('status_delivery', [3, 21])->whereNotNull('resi')->pluck('resi');


        $client = new Client();
        try {
            $response = $client->request('POST', getSetting('POPAKET_BASE_URL') . "/shipment/v1/orders/track", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('POPAKET_TOKEN')
                ],
                'body' => json_encode(['awb_numbers' => $resis])
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if (isset($responseJSON['status'])) {
                if ($responseJSON['status'] == 'success') {
                    foreach ($responseJSON['data'] as $key => $response) {
                        $transaction = Transaction::where('resi', $response['awb_number'])->first();
                        if (isset($response['tracking_code'])) {
                            if ($response['tracking_code'] == 'DELIVERED') {
                                if ($transaction && $transaction->status_delivery != 4) {
                                    $transaction->update(['status_delivery' => 4]);
                                    TransactionDeliveryStatus::create([
                                        'id_transaksi' => $transaction->id_transaksi,
                                        'delivery_status' => 4,
                                    ]);
                                    $data_notification_admin = [
                                        'invoice' => $transaction->id_transaksi,
                                        'date' => date('l,d M Y | H:i')
                                    ];
                                    createNotification('OUR200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
                                    $notification_data = [
                                        'user' => $transaction->user->name,
                                        'invoice' => $transaction->id_transaksi,
                                        'brand' => $transaction->brand->name,
                                    ];
                                    createNotification('ODS200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
                                    createNotification('TRXR200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
                                    return true;
                                }
                            }
                        }
                    }
                    return true;
                }
            }

            return 0;
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Track Order (Track Order Command)',
            ]);
            return 0;
        }
    }
}
