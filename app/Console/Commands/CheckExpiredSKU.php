<?php

namespace App\Console\Commands;

use App\Models\SkuMaster;
use Illuminate\Console\Command;

class CheckExpiredSKU extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expired-sku';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Expired Sku';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        setSetting('check:expired-sku', now()->toDateTimeString());
        $skus = SkuMaster::where('status', 1)->where('expired_at', '<', now())->get();

        foreach ($skus as $sku) {
            $now = strtotime(date('Y-m-d H:i:s'));
            $expired = strtotime($sku->expired_at);

            if ($now > $expired) {
                $sku->update([
                    'status' => 0
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
