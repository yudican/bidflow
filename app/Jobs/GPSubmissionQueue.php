<?php

namespace App\Jobs;

use App\Models\GpSusmissionLogError;
use App\Models\ListOrderGp;
use App\Models\ListOrderGpDetail;
use App\Models\OrderListByGenie;
use App\Models\SkuMaster;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class GPSubmissionQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $current_list;
    protected $order_ids;
    protected $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($current_list, $order_ids = [], $key)
    {
        $this->current_list = $current_list;
        $this->order_ids = $order_ids;
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $current_list = $this->current_list;
        // $order_ids = $this->order_ids;
        // $keyData = $this->key;
        // $temps = OrderListByGenie::whereIn('id', $order_ids)->select('id', 'channel', 'sku', 'qty', 'harga_promo', 'trx_id')->get();
        // // $product_sku = [];
        // $success = 0;
        // $error = 0;
        // $total_progress = 0;



        // // submit GP
        // $headerGp = [];
        // $detailGp = [];


        // foreach ($temps as $key => $data) {
        //     // Submit GP
        //     $custNumber = 'MP0001';
        //     $custommerId = $data->channel;
        //     $channels = explode('/', $custommerId);

        //     if (is_array($channels)) {
        //         if (count($channels) > 1) {
        //             $custNumber = $channels[1];
        //         }
        //     }


        //     $datas = [];
        //     $haveSku = null;
        //     $sku = $data->sku;
        //     $total_progress += 1;
        //     $logError = GpSusmissionLogError::where('list_order_gp_id', $current_list)->where('ginee_id', $data->id)->first();

        //     $sku_master = SkuMaster::get();
        //     foreach ($sku_master as $index => $value) {
        //         $skus = explode('-', $sku);
        //         if (isset($skus) && count($skus) > 1) {
        //             if (str_contains($skus[0], $value->sku)) {
        //                 $sku = $value->sku;
        //                 $haveSku = $value;
        //             }
        //         } else {
        //             if (str_contains($data->sku, $value->sku)) {
        //                 $sku = $value->sku;
        //                 $haveSku = $value;
        //             }
        //         }
        //     }




        //     $next = sprintf("%04d", ((int)$key + 1));
        //     $so_number = 'SO/MP/' . date('Y') . '/' . date('m') . '/' . $next;
        //     $datas['list_order_gp_id'] = $current_list;
        //     $datas['ginee_order_id'] = $data->id;
        //     $datas['so_number'] = $so_number;
        //     $datas['batch_number'] = '-';

        //     if ($haveSku) {
        //         if ($logError) {
        //             $logError->delete();
        //         }
        //         $success += 1;
        //     } else {
        //         GpSusmissionLogError::updateOrCreate(['id' => 1], [
        //             'list_order_gp_id' => $current_list,
        //             'ginee_id' => $data->id,
        //             'error_message' => $so_number . ' - SKU Tidak Ditemukan - Item Number'
        //         ]);
        //         $error += 1;
        //     }
        //     $order = ListOrderGpDetail::create($datas);

        //     $headerGp[$custNumber][$data->trx_id] = [
        //         "SOPTYPE" => 2,
        //         "DOCDATE" => date('Y-m-d H:i:s'),
        //         "CUSTNMBR" => $custNumber,
        //         "BACHNUMB" => date('YmdHi'),
        //         'CSTPONBR' => $so_number,
        //         "TRDISAMT" =>  $order->total_discount > 0 ? $order->total_discount : 0,
        //         "FREIGHT" => $order->freight_amount,
        //         "MISCAMNT" => $order->miscellaneous,
        //     ];

        //     setSetting('headerGp', json_encode($headerGp));

        //     $skuData = SkuMaster::where('sku', $sku)->where('status', 1)->first();
        //     if (isset($detailGp[$sku])) {
        //         $detailGp[$sku] = [
        //             "ITEMNMBR" => $sku,
        //             "CUSTNMBR" => $custNumber,
        //             "SOPTYPE" => 2,
        //             "QUANTITY" => $detailGp[$sku]['QUANTITY'] + $data->qty,
        //             "UOFM" => $skuData->package_name,
        //             "UNITCOST" => $order->extended_price,

        //         ];
        //     } else {
        //         $detailGp[$sku] = [
        //             "ITEMNMBR" => $sku,
        //             "CUSTNMBR" => $custNumber,
        //             "SOPTYPE" => 2,
        //             "QUANTITY" => $data->qty,
        //             "UOFM" => $skuData->package_name,
        //             "UNITCOST" => $order->extended_price,
        //         ];
        //     }

        //     GPSubmissionDetailQueue::dispatch($datas, $total_progress, count($temps), $keyData)->onQueue('queue-log');
        // }

        // GpSoSubmisionQueue::dispatch($temps, $headerGp, $detailGp, $current_list)->onQueue('queue-log');
        // ListOrderGp::find($current_list)->update(['total_failed' => $error, 'total_success' => $success]);
        // if ($error < 1) {
        //     ListOrderGp::find($current_list)->update(['status' => 'success']);
        // }
    }

    private function generateSoNo($batch_id = 1)
    {
        $year = date('Y');
        $so_number = 'SO/' . $year . '/' . $batch_id . '/';
        $data = DB::select("SELECT * FROM `tbl_list_order_gp_details` where so_number like '%$so_number%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->so_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'SO/' . $year . '/' . $batch_id . '/' . $next;
            }
        } else {
            $nomor = 'SO/' . $year . '/' . $batch_id . '/' . '000000001';
        }
        return $nomor;
    }
}
