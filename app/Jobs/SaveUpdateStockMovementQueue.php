<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SaveUpdateStockMovementQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $warehouse_id;
    protected $company_id;
    protected $product_id;
    protected $date;
    protected $type;
    protected $stock_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($warehouse_id, $company, $product_id, $now, $type, $stock_id)
    {
        $this->warehouse_id = $warehouse_id;
        $this->company_id = $company;
        $this->product_id = $product_id;
        $this->date = $now;
        $this->type = $type;
        $this->stock_id = $stock_id;
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
        $product_id = $this->product_id;
        $date = $this->date;
        $type = $this->type;
        $stock_id = $this->stock_id;
        $data = [];

        if ($type == 'in_purchase_order') {
            $in_purchase_order = DB::table('inventory_items as ii')
                ->select(DB::raw('SUM(tbl_ii.qty) as qty_total'))
                ->join('inventory_product_stocks as ips', 'ii.uid_inventory', '=', 'ips.uid_inventory')
                ->where('ips.warehouse_id', $warehouse_id)
                ->where('ips.company_id', $company)
                ->where('ii.product_id', $product_id)
                ->where('ips.status', 'done')
                ->where('ips.inventory_status', 'alocated')
                ->where('ips.inventory_type', 'received')
                ->whereDate('ips.updated_at', $date)
                ->first();
            if ($in_purchase_order) {
                $data['in_purchase_order'] = $out_sales_order?->qty_total ?? 0;
            }
        }

        if ($type == 'in_transfer') {
            // In Transfer
            $in_transfer = DB::table('inventory_detail_items as idi')
                ->select(DB::raw('SUM(tbl_idi.qty_alocation) as qty_total'))
                ->join('inventory_product_stocks as ips', 'idi.uid_inventory', '=', 'ips.uid_inventory')
                ->where('ips.destination_warehouse_id', $warehouse_id)
                ->where('ips.company_id', $company)
                ->where('idi.product_id', $product_id)
                ->where('ips.status', 'done')
                ->where('ips.inventory_status', 'alocated')
                ->where('ips.inventory_type', 'transfer')
                ->whereDate('ips.updated_at', $date)
                ->first();

            if ($in_transfer) {
                $data['in_transfer'] = $in_transfer?->qty_total ?? 0;
            }
        }

        if ($type == 'in_sales_return') {
            // IN Sales RETURN
            $in_sales_return = DB::table('inventory_items as ii')
                ->select(DB::raw('SUM(tbl_ii.qty_diterima) as qty_total'))
                ->join('inventory_product_returns as ipr', 'ii.uid_inventory', '=', 'ipr.uid_inventory')
                ->where('ipr.warehouse_id', $warehouse_id)
                ->where('ipr.company_id', $company)
                ->where('ii.product_id', $product_id)
                ->where('ii.type', 'return-prcved')
                ->where('ii.received_vendor', 2)
                ->where('ipr.status', 1)
                ->whereDate('ipr.updated_at', $date)
                ->first();
            if ($in_sales_return) {
                $data['in_sales_return'] = $out_sales_order?->qty_total ?? 0;
            }
        }

        if ($type == 'out_return_suplier') {
            $out_return_suplier = DB::table('inventory_items as ii')
                ->select(DB::raw('SUM(tbl_ii.qty_diterima) as qty_total'))
                ->join('inventory_product_returns as ipr', 'ii.uid_inventory', '=', 'ipr.uid_inventory')
                ->where('ipr.warehouse_id', $warehouse_id)
                ->where('ipr.company_id', $company)
                ->where('ii.product_id', $product_id)
                ->where('ii.type', 'return-prcved')
                ->where('ii.received_vendor', 1)
                ->where('ipr.status', 1)
                ->whereDate('ipr.updated_at', $date)
                ->first();
            if ($out_return_suplier) {
                $data['out_return_suplier'] = $out_sales_order?->qty_total ?? 0;
            }
        }

        if ($type == 'out_transfer') {
            // OUT Transfer
            $out_transfer = DB::table('inventory_detail_items as idi')
                ->select(DB::raw('SUM(tbl_idi.qty_alocation) as qty_total'))
                ->join('inventory_product_stocks as ips', 'idi.uid_inventory', '=', 'ips.uid_inventory')
                ->where('ips.warehouse_id', $warehouse_id)
                ->where('ips.company_id', $company)
                ->where('idi.product_id', $product_id)
                ->where('ips.status', 'done')
                ->where('ips.inventory_status', 'alocated')
                ->where('ips.inventory_type', 'transfer')
                ->whereDate('ips.updated_at', $date)
                ->first();
            if ($out_transfer) {
                $data['out_transfer'] = $out_transfer?->qty_total ?? 0;
            }
        }

        if ($type == 'out_sales_order') {
            $out_sales_order = DB::table('order_deliveries as od')
                ->select(DB::raw('SUM(tbl_od.qty_delivered * tbl_pv.qty_bundling) as qty_total'))
                ->join('order_manuals as om', 'od.uid_lead', '=', 'om.uid_lead')
                ->join('product_needs as pn', 'od.product_need_id', '=', 'pn.id')
                ->join('product_variants as pv', 'pn.product_id', '=', 'pv.id')
                ->where('od.status', '!=', 'cancel')
                ->where('om.warehouse_id', $warehouse_id)
                ->where('om.company_id', $company)
                ->where('pv.product_id', $product_id)
                ->whereDate('od.created_at', $date)
                ->first();
            if ($out_sales_order) {
                $data['out_sales_order'] = $out_sales_order?->qty_total ?? 0;
            }
        }

        DB::table('stock_movements')->updateOrInsert(['id' => $stock_id], $data);
    }
}
