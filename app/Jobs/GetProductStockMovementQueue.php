<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GetProductStockMovementQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $warehouse_id;
    protected $company_id;
    protected $now;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($warehouse_id, $company_id, $now)
    {
        $this->warehouse_id = $warehouse_id;
        $this->company_id = $company_id;
        $this->now = $now;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $warehouse_id = $this->warehouse_id;
        $company = $this->company_id;
        $now = Carbon::parse($this->now)->format('Y-m-d');

        $products = DB::table('products')->where('status', 1)->whereNull('deleted_at')->select('id', 'sku')->get();

        foreach ($products as $key => $product) {
            $stock = DB::table('stock_movements')->where('warehouse_id', $warehouse_id)->where('product_id', $product->id)->where('company_id', $company)->whereDate('created_at', $now)->first();
            $stock_id = $stock?->id;
            if (!$stock) {
                $stock_master = DB::table('vw_product_stocks_master')->where('warehouse_id', $warehouse_id)->where('company_id', $company)->where('product_id', $product->id)->select('stock')->first();
                $stock_id = DB::table('stock_movements')->insertGetId([
                    'begin_stock' => $stock_master?->stock ?? 0,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'company_id' => $company,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            SaveUpdateStockMovementQueue::dispatch($warehouse_id, $company, $product->id, $now, 'in_purchase_order', $stock_id)->onQueue('queue-backend');
            SaveUpdateStockMovementQueue::dispatch($warehouse_id, $company, $product->id, $now, 'in_transfer', $stock_id)->onQueue('queue-backend');
            SaveUpdateStockMovementQueue::dispatch($warehouse_id, $company, $product->id, $now, 'in_sales_return', $stock_id)->onQueue('queue-backend');
            SaveUpdateStockMovementQueue::dispatch($warehouse_id, $company, $product->id, $now, 'out_return_suplier', $stock_id)->onQueue('queue-backend');
            SaveUpdateStockMovementQueue::dispatch($warehouse_id, $company, $product->id, $now, 'out_transfer', $stock_id)->onQueue('queue-backend');
            SaveUpdateStockMovementQueue::dispatch($warehouse_id, $company, $product->id, $now, 'out_sales_order', $stock_id)->onQueue('queue-backend');
        }
    }
}
