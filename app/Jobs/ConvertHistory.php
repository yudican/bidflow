<?php

namespace App\Jobs;

use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\ProductConvertHistory;
use App\Models\ProductImportTemp;
use App\Models\ProductSku;
use App\Models\ProductVariant;
use App\Models\SkuMaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class ConvertHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $convert_id;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($convert_id, $user_id)
    {
        $this->convert_id = $convert_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $convert_id = $this->convert_id;
        $user_id = $this->user_id;
        $temps = ProductImportTemp::where('user_id', $user_id)->where('status_import', 0)->get();
        // $product_sku = [];
        $success = 0;
        $error = 0;

        foreach ($temps as $key => $data) {
            $datas = [];
            $haveSku = null;
            $sku = $data->sku;

            $sku_master = SkuMaster::get();
            foreach ($sku_master as $key => $value) {
                $skus = explode('-', $sku);
                if (isset($skus) && count($skus) > 1) {
                    if (str_contains($skus[0], $value->sku)) {
                        $sku = $value->sku;
                        $haveSku = $value;
                    }
                } else {
                    if (str_contains($data->sku, $value->sku)) {
                        $sku = $value->sku;
                        $haveSku = $value;
                    }
                }
            }
            $datas['product_convert_id'] = $convert_id;
            $datas['sku'] = $sku;
            $datas['produk_nama'] = $data->produk_nama;
            $datas['qty'] = $data->qty;
            $datas['toko'] = $data->toko;
            $datas['harga_awal'] = $data->harga_awal;
            $datas['harga_promo'] = $data->harga_promo;
            $datas['ongkir'] = $data->ongkir;
            $datas['tanggal_transaksi'] = $data->tanggal_transaksi ? date('Y-m-d H:i:s', strtotime($data->tanggal_transaksi)) : date('Y-m-d H:i:s');
            $datas['harga_satuan'] = $data->harga_awal;
            $datas['subtotal'] = $data->harga_awal * $data->qty + $data->ongkir;
            $datas['status_convert'] = 0;
            if ($haveSku) {
                $data->update(['status_convert' => 'success']);
                $success += 1;
                $datas['status_convert'] = 1;
            } else {
                $error += 1;
                $data->update(['status_convert' => 'failed']);
            }
            ConvertHistoryItem::dispatch($datas, $user_id)->onQueue('queue-log');
        }


        // if ($error < 1) {
        //     ProductImportTemp::where('user_id', $user_id)->where('status_import', 0)->update(['status_import' => 1]);
        //     ProductConvert::find($convert_id)->update(['status' => 'success']);
        //     removeSetting('product_convert_id_' . $user_id);
        //     removeSetting('product_import_count_' . $user_id);
        //     removeSetting('product_import_success_' . $user_id);
        //     removeSetting('product_convert_count_' . $user_id);
        //     removeSetting('product_convert_success_' . $user_id);
        // }
        // ProductSku::insert($product_sku);
    }
}
