<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Jobs\GetOrderListFromGenie;
use App\Jobs\GPSubmissionQueue;
use App\Jobs\TestQueue;
use App\Exports\GenieOrderExport;
use App\Models\EthixMarketPlace;
use App\Models\EthixMaster;
use App\Models\OrderListByGenie;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Pusher\Pusher;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class MpethixController extends Controller
{
    public function index($detail = null)
    {
        return view('spa.spa-index');
    }

    public function dashboardDetail(Request $request)
    {
        $order = EthixMaster::where('type', 'marketplace')->get();

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
            'receipent_name' => $order->receipent_name,
            'receipent_phone' => $order->receipent_phone,
            'receipent_address' => $order->receipent_address
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
        $channel = $request->channel_origin;
        $sku = $request->sku;
        $invoice_number = $request->invoice_number;
        $tanggal_transaksi = $request->tanggal_transaksi;
        $status = $request->status;
        $orderList = EthixMarketPlace::query();
        if ($search) {
            $orderList->where('channel_origin', 'like', "%$search%");
            $orderList->orWhere('shop_name', 'like', "%$search%");
            $orderList->orWhere('name', 'like', "%$search%");
        }

        if ($sku) {
            $orderList->where('sku', 'like', "%$sku%");
        }

        if ($channel) {
            $orderList->orWhereIn('channel_origin', $channel);
        }

        if ($status) {
            $orderList->orWhereIn('status', $status);
        }

        if ($invoice_number) {
            $orderList->orWhereIn('invoice_number', $invoice_number);
        }

        if ($tanggal_transaksi) {
            $orderList->orWhereBetween('created_at', $tanggal_transaksi);
        }

        $orders = $orderList->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function detail($orderId)
    {
        $ethix = EthixMarketPlace::with('items')->find($orderId);

        if ($ethix) {
            return response()->json([
                'status' => 'success',
                'data' =>  $ethix,
                'message' => 'detail order'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => null,
            'message' => 'detail order'
        ], 404);
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
