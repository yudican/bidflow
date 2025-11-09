<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\TransactionAgent;
use Illuminate\Console\Command;

class ReminderProccessOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminderorder:proccess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder Proccess Order';

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
        $transactions = [];
        $transaction_custommer = Transaction::where('status', 3)->get();
        $transaction_agent = TransactionAgent::where('status', 3)->get();

        foreach ($transaction_custommer as $key => $custommer) {
            $transactions[] = $custommer;
        }
        foreach ($transaction_agent as $key => $agent) {
            $transactions[] = $agent;
        }

        foreach ($transactions as $key => $transaction) {
            $now = strtotime(date('Y-m-d H:i:s'));
            $end_date = strtotime($transaction->created_at->addDays(1)->format('Y-m-d H:i:s'));
            if ($now >= $end_date) {
                createNotification(
                    'QOP200',
                    [],
                    [
                        'invoice' => $transaction->id_transaksi,
                        'rincian_transaksi' => getRincianTransaksi($transaction),
                    ]
                );
            }
        }
        return 0;
    }
}
