<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Jobs\GetOrderListFromGenie;
use App\Jobs\GPSubmissionQueue;
use App\Jobs\TestQueue;
use App\Exports\GenieOrderExport;
use App\Models\ListOrderGp;
use App\Models\OrderListByGenie;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class GenieController extends Controller
{
    public function index($detail = null)
    {
        return view('spa.spa-index');
    }

    public function syncData()
    {
        OrderListByGenie::where('status_sync', 1)->update(['status_sync' => 0]);
        GetOrderListFromGenie::dispatch()->onQueue('queue-log');
        setSetting('sync', 'true');
        return response()->json([
            'status' => 'success',
            'message' => 'Sync Data Sedang Berlangsung'
        ]);
    }

    public function cancelSync()
    {
        setSetting('sync', 'false');
        removeSetting('genie_order_list_total');
        removeSetting('genie_order_list_success_total');
        DB::table('jobs')->truncate();
        OrderListByGenie::where('status_sync', 1)->update(['status_sync' => 0]);
        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher(
            'eafb4c1c4f906c90399e',
            '01d9b57c3818c1644cb0',
            '1472093',
            $options
        );
        $pusher->trigger('aimi', 'progress', ['total' => 0, 'success' => 0, 'status' => 'finish', 'percentage' => 100]);
        return response()->json([
            'status' => 'success',
            'message' => 'Sync Data Berhasil Dibatalkan'
        ]);
    }

    public function submitGp(Request $request)
    {
        $key = 'total_submit_gp_' . auth()->user()->id;
        setSetting($key, $request->total);
        $listGp = ListOrderGp::create([
            'create_date' => date('Y-m-d H:i:s'),
            'submit_by' => auth()->user()->id,
            'status' => 'failed',
            'tax_name' => $request->tax_name,
            'tax_value' => $request->tax_value,
            'total_success' => 0,
            'total_failed' => 0,
        ]);
        GPSubmissionQueue::dispatch($listGp->id, $request->order_ids, $key)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Submit Data Sedang Berlangsung'
        ]);
    }

    public function checkSync()
    {
        $total_data = getSetting('genie_order_list_total');
        $current_data = getSetting('genie_order_list_success_total');
        $percentage = getPercentage($current_data, $total_data);

        if (getSetting('sync')) {
            if ($total_data == $current_data) {
                $this->cancelSync();
            }
        }


        return response()->json([
            'status' => 'success',
            'data' => [
                'sync' => getSetting('sync'),
                'total' => $total_data,
                'success' => $current_data,
                'percentage' => $percentage
            ]
        ]);
    }

    public function dashboardDetail(Request $request)
    {
        $order = OrderListByGenie::query();

        if ($request->channel) {
            $order->whereIn('channel', $request->channel);
        }

        if ($request->shopId) {
            $order->whereIn('shopId', $request->shopId);
        }


        if ($request->time) {
            if (in_array($request->time, ['yesterday', 'week', 'month', 'year'])) {
                if ($request->time == 'yesterday') {
                    $order->whereDate('tanggal_transaksi', date('Y-m-d', strtotime('-1 day')));
                } else if ($request->time == 'week') {
                    $order->whereBetween('tanggal_transaksi', [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))]);
                } else if ($request->time == 'month') {
                    $order->whereMonth('tanggal_transaksi', date('m'));
                } else if ($request->time == 'year') {
                    $order->whereYear('tanggal_transaksi', date('Y'));
                }
            } else {
                $order->whereBetween('tanggal_transaksi', $request->time);
            }
        }


        $orders = $order->get();
        $charts = [];

        foreach ($orders as $key => $item) {
            $charts[] = [
                'time' => $item->tanggal_transaksi ? strtotime($item->tanggal_transaksi) * 1000 : null,
                'total_order_number' => $item->qty,
                'total_order_amount' => $item->nominal,
            ];
        }

        $temp = [];
        foreach ($charts as $value) {
            //check if time exists in the temp array
            if (array_key_exists($value['time'], $temp)) {
                //Add up the values from each time
                $temp[$value['time']]['total_order_number'] += 1;
                $temp[$value['time']]['total_order_amount'] += $value['total_order_amount'] ?? 0;
            } else {
                $temp[$value['time']] = $value;
            }
        }
        $chart_list = [];
        foreach ($temp as $key => $item_list) {
            $chart_list[] = $item_list;
        }

        $data = [
            'total_biaya_komisi' => $order->sum('biaya_komisi'),
            'total_biaya_layanan' => $order->sum('biaya_layanan'),
            'total_ongkir_dibayar_sistem' => $order->sum('ongkir_dibayar_sistem'),
            'total_order_number' => $order->sum('qty'),
            'total_order' => $order->sum('nominal'),
            'total_order_amount' => $order->count('id'),
            'total_refund_amount' => $order->sum('jumlah_pengambalian_dana'),
            'total_return' => $order->where('status', 'RETURNED')->count('id'),
            'charts' => $chart_list,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'dashboard' => $data,
                'stores' => $this->loadStore()
            ]
        ]);
    }

    public function orderList(Request $request)
    {
        // search
        $search = $request->search;
        $channel = $request->channel;
        $sku = $request->sku;
        $tanggal_transaksi = $request->tanggal_transaksi;
        $status = $request->status;
        $orderList = OrderListByGenie::query();
        if ($search) {
            $orderList->where('trx_id', 'like', "%$search%");
            $orderList->orWhere('user', 'like', "%$search%");
            $orderList->orWhere('channel', 'like', "%$search%");
            $orderList->orWhere('store', 'like', "%$search%");
            $orderList->orWhere('sku', 'like', "%$search%");
            $orderList->orWhere('nama_produk', 'like', "%$search%");
            $orderList->orWhere('metode_pembayaran', 'like', "%$search%");
            $orderList->orWhere('tanggal_transaksi', 'like', "%$search%");
            $orderList->orWhere('status_pengiriman', 'like', "%$search%");
        }

        if ($sku) {
            $orderList->where('sku', 'like', "%$sku%");
        }

        if ($channel) {
            $orderList->orWhereIn('channel', $channel);
        }

        if ($status) {
            $orderList->orWhereIn('status', $status);
        }

        if ($tanggal_transaksi) {
            $orderList->orWhereBetween('tanggal_transaksi', $tanggal_transaksi);
        }

        $orders = $orderList->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function detail($orderId)
    {
        $client = new Client();
        $signature = base64_encode(hash_hmac('sha256', "POST$/openapi/order/v1/get$", getSetting('GINEE_SECRET_KEY'), true));

        try {
            $response = $client->request('POST', getSetting('GINEE_URL') . '/openapi/order/v1/get', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Advai-Country' => 'ID',
                    'Authorization' => getSetting('GINEE_ACCESS_KEY') . ':' . $signature
                ],
                'body' => json_encode([
                    'orderId' => $orderId
                ]),
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if ($responseJSON['code'] == 'SUCCESS') {
                return response()->json([
                    'status' => 'success',
                    'data' => $responseJSON['data']
                ]);
            }
        } catch (ClientException $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }

    public function loadStore()
    {
        $client = new Client();
        $signature = base64_encode(hash_hmac('sha256', "POST$/openapi/shop/v1/list$", 'b3436f168a4402b7', true));

        try {
            $response = $client->request('POST', 'https://genie-sandbox.advai.net/openapi/shop/v1/list', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Advai-Country' => 'ID',
                    'Authorization' => 'd20254aee13cc156:' . $signature
                ],
                'body' => json_encode([
                    'body' => json_encode([
                        "page" => 0,
                        "size" => 100
                    ]),
                ]),
            ]);
            $responseJSON = json_decode($response->getBody(), true);
            if ($responseJSON['code'] == 'SUCCESS') {
                if (isset($responseJSON['data']['content'])) {
                    $stores = [];
                    foreach ($responseJSON['data']['content'] as $key => $content) {
                        $stores[] = [
                            'shopId' => $content['shopId'],
                            'store_name' => $content['name'],
                            'channel' => $content['channel']
                        ];
                    }

                    return $stores;
                }
                return [];
            }
        } catch (ClientException $th) {
            return [];
        }
    }

    public function export()
    {
        $file_name = 'convert/data-genie-order-' . date('Y-m-d') . '.xlsx';

        Excel::store(new GenieOrderExport(), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
}
