<?php

namespace App\Imports\Marketplace;

use App\Models\GpCustomer;
use App\Models\MPOrderList;
use App\Models\MPOrderListImportLogs;
use App\Models\MPOrderListItems;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;

class MarketPlaceImportOrder implements ToArray
{
    /**
     * @param array $collection
     */
    public function array(array $orders)
    {
        $new_orders = [];
        foreach ($orders as $key => $order) {
            if ($key > 0) {
                $new_orders[$order[0]][] = $order;
            }
        }
        foreach (array_values($new_orders) as $row_key => $items) {
            $orderSaved = null;
            foreach ($items as $key => $item) {
                try {
                    if ($key == 0) {
                        $store = GpCustomer::where('customer_name', 'like', '%' . $item[3] . '%')->first();
                        $orderData = [
                            'trx_id' => $item[0],
                            'customer_code' => $store ? $store->customer_id : '-',
                            'customer_name' =>  $item[2],
                            'channel' =>  $item[3],
                            'store' =>  $item[4],
                            'amount' =>  $item[11],
                            'shipping_fee' =>  $item[10],
                            'shipping_fee_non_cashlesh' => $item[10],
                            'platform_rebate' => $item[11],
                            'voucher_seller' => $item[12],
                            'shipping_fee_deference' => $item[13],
                            'platform_fulfilment' => $item[14],
                            'service_fee' => $item[15],
                            'payment_method' =>  $item[20],
                            'warehouse' =>  $item[22],
                            'mp_fee' => $item[15],
                            'discount' => $item[12],
                            'trx_date' => $item[24],
                            'courir' => $item[25],
                            'awb' => $item[26],
                            'status' => $item[27],
                            'shipping_status' => $item[28],

                        ];

                        $orderSaved = MPOrderList::updateOrCreate(['trx_id' => $item[0]], $orderData);
                    }


                    MPOrderListItems::updateOrCreate([
                        'mp_order_list_id' => $orderSaved->id,
                        'sku' =>  $item[5],
                    ], [
                        'mp_order_list_id' => $orderSaved->id,
                        'sku' =>  $item[5],
                        'product_name' =>  $item[6],
                        'price' =>  $item[7],
                        'final_price' =>  $item[9],
                        'qty' =>  $item[8]
                    ]);

                    MPOrderListImportLogs::create([
                        'mp_order_list_id' => $orderSaved->id,
                        'status' => 'success',
                        'message' => $item[0] . ' - Berhasil Diimport',
                    ]);
                } catch (\Throwable $th) {
                    MPOrderListImportLogs::create([
                        'mp_order_list_id' => 0,
                        'status' => 'failed',
                        'message' => $item[0] . ' - ' . $th->getMessage(),
                    ]);
                }
                $orderSaved = MPOrderList::where('trx_id', $item[0])->first();
            }
        }
    }
}
