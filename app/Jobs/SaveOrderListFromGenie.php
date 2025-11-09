<?php

namespace App\Jobs;

use App\Models\GpCustomer;
use App\Models\OrderListByGenie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class SaveOrderListFromGenie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    public $success_total;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = [], $success_total = 0)
    {
        $this->data = $data;
        $this->success_total = $success_total;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;

        foreach ($data['orderItems'] as $key => $item) {
            $logistic = count($item['logisticsInfos']) > 0 ? $item['logisticsInfos'][0] : [];
            $channel = $data['channelId'];
            if ($data['paymentMethod'] == 'COD') {
                $channel = $data['channelId'] . ' ' . $data['paymentMethod'];
            }
            $channelResult = str_replace('_ID', '', $channel);
            $store = GpCustomer::where('customer_name', 'like', '%' . $channelResult . '%')->first();
            $data_to_store = [
                'trx_id' => $data['id'],
                'shopId' => $data['shopId'],
                'user' => $data['customerName'],
                'channel' => $store ? $data['channelId'] . '/' . $store->customer_id : $data['channelId'],
                'store' => $store ? $store->customer_name : 'Flimty Official Store',
                'sku' => $item['sku'],
                'nama_produk' => $item['productName'],
                'harga_awal' => $item['originalPrice'],
                'harga_promo' => $item['actualPrice'],

                'qty' => $item['quantity'],
                'nominal' => $data['sellerTotalAmount'],
                'ongkir' => $data['paymentInfo']['totalShippingFee'],
                'pajak' => $data['paymentInfo']['taxationFee'],
                'asuransi' => $data['paymentInfo']['insuranceFee'],
                'total_diskon' => $data['paymentInfo']['totalDiscounts'],
                'biaya_komisi' => $data['paymentInfo']['commissionFee'],
                'biaya_layanan' => $data['paymentInfo']['serviceFee'],

                'ongkir_dibayar_sistem' => $data['paymentInfo']['finalShippingFee'],
                'potongan_harga' => $data['paymentInfo']['sellerRebate'],
                'subsidi_angkutan' => $data['paymentInfo']['discountPlatform'],
                'koin' => $data['paymentInfo']['coin'],
                'loin_cashback' => $data['paymentInfo']['coinCashBack'],
                'jumlah_pengambalian_dana' => $data['paymentInfo']['sellerReturnRefundAmount'],
                'voucher_channel' => $data['paymentInfo']['voucherPlatform'],
                'diskon_penjual' => $data['paymentInfo']['voucherSeller'],

                'biaya_lacanan_kartu_kredit' => $data['paymentInfo']['creditCardServiceFee'],
                'metode_pembayaran' => $data['paymentMethod'],
                'diskon' => 0,
                'tanggal_transaksi' => date('Y-m-d H:i:s', strtotime($data['externalCreateDatetime'])),
                'resi' => isset($logistic['logisticsTrackingNumber']) ? $logistic['logisticsTrackingNumber'] : null,
                'kurir' => isset($logistic['logisticsProviderName']) ? $logistic['logisticsProviderName'] : null,
                'status' => $data['externalOrderStatus'],
                'status_pengiriman' => $data['orderStatus'],
                'status_sync' => 1,
            ];

            OrderListByGenie::updateOrCreate([
                'trx_id' => $data['id'],
                'sku' => $item['sku']
            ], $data_to_store);
        }
    }
}
