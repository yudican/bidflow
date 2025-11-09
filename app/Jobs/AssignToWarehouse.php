<?php

namespace App\Jobs;

use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\TransactionDeliveryStatus;
use App\Models\TransactionStatus;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Livewire\ComponentConcerns\ReceivesEvents;

class AssignToWarehouse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ReceivesEvents;
    protected $transaction;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = $this->transaction;
        DB::beginTransaction();
        try {
            $transaction->update(['status' => 7, 'status_delivery' => 1]);
            TransactionStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'status' => 7,
            ]);
            TransactionDeliveryStatus::create([
                'id_transaksi' => $transaction->id_transaksi,
                'delivery_status' => 1,
            ]);

            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $transaction->id, 'keterangan' => 'Assign Warehouse']);
            // create notification
            $data_notification_admin = [
                'user' => auth()->user()->name,
                'rincian_bayar' => getRincianPembayaran($transaction),
                'rincian_transaksi' => getRincianTransaksi($transaction),
            ];
            createNotification('WPO200', [], $data_notification_admin, ['transaction_id' => $transaction->id]);
            $notification_data = [
                'user' => $transaction->user->name,
                'invoice' => $transaction->id_transaksi,
            ];
            createNotification('ODP200', ['user_id' => $transaction->user->id, 'other_id' => $transaction->id], $notification_data, ['transaction_id' => $transaction->id]);
            DB::commit();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        } catch (ClientException $th) {
            $response = $th->getResponse();
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $response->getBody()->getContents(),
                'action' => 'Assign To warehouse (' . $transaction->id_transaksi . ')',
            ]);
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }
}
