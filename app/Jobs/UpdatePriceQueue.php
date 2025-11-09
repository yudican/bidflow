<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class UpdatePriceQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    protected $table;
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order = null, $table = 'order_manuals', $type = 'manual')
    {
        $this->order = $order;
        $this->table = $table;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;
        $table = $this->table;
        $type = $this->type;
        $tax_percentage = 0;
        $discount_amount = 0;
        $subtotal = 0;

        $items = DB::table('product_needs as pn')
            ->leftJoin('master_tax as tax', 'pn.tax_id', '=', 'tax.id')
            ->leftJoin('master_discounts as disc', 'pn.discount_id', '=', 'disc.id')
            ->select('pn.qty', 'pn.price', 'pn.discount', 'pn.discount_id', 'disc.percentage', 'tax.tax_percentage')->where('pn.uid_lead', $order->uid_lead)->get();

        if ($table == 'order_transfers') {
            $items = DB::table('inventory_detail_items as pn')
                ->leftJoin('master_tax as tax', 'pn.tax_id', '=', 'tax.id')
                ->select('pn.qty', 'pn.price_nego as price', 'pn.discount', 'tax.tax_percentage')->where('pn.uid_inventory', $order->uid_lead)->get();
        }


        foreach ($items as $key => $item) {
            if (isset($item?->discount_id) && $item?->discount_id > 0) {
                $discount_percent = $item->percentage > 0 ?  $item->percentage / 100 : 0;
                $discount_amount += $discount_percent * $item->price;
            } else {
                if ($item->discount > 0) {
                    $discount_amount += $item->discount * $item->qty;
                }
            }
            $tax_percentage =  (float) $item->tax_percentage > 0 ? $item->tax_percentage / 100 : 0;
            $subtotal += $item?->price;
        }

        $dpp = $subtotal - $discount_amount;
        $ppn = $dpp * $tax_percentage;
        $total = 0;
        if ($table == 'order_transfers') {
            $total = intval($dpp + $ppn);
        } else {
            $total = intval($dpp + $ppn + $order->kode_unik + $order->ongkir);
        }

        DB::table($table)->where('uid_lead', $order?->uid_lead)->update([
            'subtotal' => $subtotal,
            'diskon' => $discount_amount,
            'dpp' => $dpp,
            'tax_percentage' => (float) $tax_percentage,
            'ppn' => floor($ppn),
            'total' => $total,
        ]);
    }
}
