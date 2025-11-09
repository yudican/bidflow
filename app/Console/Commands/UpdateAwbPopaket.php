<?php

namespace App\Console\Commands;

use App\Jobs\CreateOrderPopaket;
use App\Jobs\GetOrderResi;
use App\Jobs\RequestNewAwbNumber;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use Illuminate\Console\Command;

class UpdateAwbPopaket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'popaket:awb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Paket Awb';

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
        $transactions = Transaction::where('status', 7)->where('status_delivery', 1)->whereNull('resi')->get();

        foreach ($transactions as $key => $transaction) {
            if ($transaction['awb_status'] == 0) {
                CreateOrderPopaket::dispatch($transaction)->onQueue('queue-backend');
            }
            if ($transaction['awb_status'] == 2) {
                GetOrderResi::dispatch($transaction->id_transaksi)->onQueue('queue-backend');
            }
        }

        return 0;
    }
}
