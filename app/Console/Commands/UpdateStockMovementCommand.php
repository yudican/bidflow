<?php

namespace App\Console\Commands;

use App\Jobs\GetProductStockMovementQueue;
use App\Jobs\SaveUpdateStockMovementQueue;
use App\Models\Product;
use App\Models\ProductStock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStockMovementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:stock-movement';

    /**
     * The console Create Log Stock Movement.
     *
     * @var string
     */
    protected $description = 'Create Log Stock Movement';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $companies = [1, 2];
        $warehouses = DB::table('warehouses')->select('id')->get();
        $now = Carbon::now();

        foreach ($companies as $key => $company) {
            foreach ($warehouses as $key => $warehouse) {
                $warehouse_id = $warehouse->id;
                GetProductStockMovementQueue::dispatch($warehouse_id, $company, $now)->onQueue('queue-backend');
            }
        }

        return Command::SUCCESS;
    }
}
