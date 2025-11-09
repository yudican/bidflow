<?php

namespace App\Jobs;

use App\Models\ProductImportTemp;
use App\Models\ProductVariant;
use App\Models\SkuMaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ProductTempImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        if ($data[0] != 'TRX ID') {
            $sku = isset($data[4]) ? $data[4] : '-';
            $qty = isset($data[8]) ? $data[8] : 1;

            $produk_nama = isset($data[5]) ? $data[5] : '';

            $datas = [];
            $datas = [
                'trx_id' => isset($data[0]) ? $data[0] : '',
                'user' => isset($data[1]) ? $data[1] : '',
                'channel' => isset($data[2]) ? $data[2] : '',
                'toko' => isset($data[3]) ? $data[3] : '',
                'sku' => $sku,
                'produk_nama' => $produk_nama,
                'harga_awal' => isset($data[6]) ? $data[6] : '',
                'harga_promo' => isset($data[7]) ? $data[7] : '',
                'qty' => $qty,
                'ongkir' => isset($data[10]) ? $data[10] : '',
                'metode_pembayaran' => isset($data[25]) ? $data[25] : '',
                'diskon' => isset($data[26]) ? $data[26] : '',
                'tanggal_transaksi' => isset($data[27]) ? $data[27] : '',
                'kurir' => isset($data[28]) ? $data[28] : '',
                'resi' => isset($data[29]) ? $data[29] : '',
                'status' => isset($data[30]) ? $data[30] : '',
                'user_id' => isset($data['user_id']) ? $data['user_id'] : '',
                'status_import' => 0,
                'status_convert' => 'failed',
            ];

            $sku_master = SkuMaster::all();
            foreach ($sku_master as $key => $value) {
                if (str_contains($sku, $value->sku)) {
                    $datas['status_convert'] = 'success';
                    $datas['status_import'] = 0;
                    $datas['user_id'] = isset($data['user_id']) ? $data['user_id'] : '';
                }

                if ($sku == $value->sku) {
                    $sku = $value->sku;
                    $datas['sku'] = $value->sku;
                    $datas['status_convert'] = 'success';
                    $datas['status_import'] = 0;
                    $datas['user_id'] = isset($data['user_id']) ? $data['user_id'] : '';
                }
            }

            $skus = explode('-', $sku);
            if (isset($skus) && count($skus) > 1) {
                $variant = ProductVariant::where('sku', $skus[0])->where('sku_variant', 'like', '%' . $skus[1] . '%')->first();
                if ($variant) {
                    if ($variant->qty_bundling > 0) {
                        $datas['qty'] = $variant->qty_bundling * $qty;
                    }
                }
            }


            $product_variant = ProductVariant::where('sku', $sku)->first();
            if ($product_variant) {
                // $datas['sku'] = $sku;
                if ($product_variant->product) {
                    $datas['produk_nama'] = $product_variant->product->name;
                    // $datas['harga_awal'] = $product_variant->product->price['basic_price'];
                    // $datas['harga_promo'] = $product_variant->product->price['final_price'];
                    // $datas['qty'] = $product_variant->product->stock;
                }
                $datas['status_convert'] = 'success';
                $datas['status_import'] = 0;
                $datas['user_id'] = isset($data['user_id']) ? $data['user_id'] : '';
            }

            ProductImportTemp::create($datas);
            $success = getSetting('product_import_success_' . $data['user_id']) ?? 0;
            setSetting('product_import_success_' . $data['user_id'], $success + 1);
            importProgress($data['user_id'], $success + 1);
        }
    }
}
