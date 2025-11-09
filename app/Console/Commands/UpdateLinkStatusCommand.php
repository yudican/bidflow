<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class UpdateLinkStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:link-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $transactions = Transaction::where('status', 0)->where('status_delivery', 0)->where('expire_payment', '<', now())->get();

        foreach ($transactions as $transaction) {
            $now = strtotime(date('Y-m-d H:i:s'));
            $expired = strtotime($transaction->expire_payment);

            if ($now > $expired) {
                $transaction->update([
                    'status_link' => 0,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
