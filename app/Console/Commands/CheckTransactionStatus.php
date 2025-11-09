<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\TransactionStatus;
use Illuminate\Console\Command;

class CheckTransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkstatus:transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check transaction Status';

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
        $transactions = Transaction::where('status', 1)->get();
        $now = strtotime(date('Y-m-d H:i:s'));
        foreach ($transactions as $transaction) {
            if ($transaction->expire_payment) {
                $transaction_exp = strtotime($transaction->expire_payment);

                if ($now > $transaction_exp) {
                    $transaction->update(['status' => 6]);
                    TransactionStatus::create([
                        'id_transaksi' => $transaction->id_transaksi,
                        'status' => 6,
                    ]);
                    createNotification('ORC400', ['user_id' => $transaction->user_id, 'other_id' => $transaction->id], ['brand' => $transaction->brand->name], ['transaction_id' => $transaction->id]);
                }
            }
        }

        return 0;
    }
}
