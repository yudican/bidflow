<?php

namespace App\Console\Commands;

use App\Jobs\GetOrderListFromGenie;
use Illuminate\Console\Command;

class GetDataOrderByGenie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ginie:order-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Order List From Genie';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!getSetting('sync')) {
            GetOrderListFromGenie::dispatch()->onQueue('queue-log');
        }
        return 0;
    }
}
