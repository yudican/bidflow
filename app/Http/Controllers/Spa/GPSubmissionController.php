<?php

namespace App\Http\Controllers\Spa;

use App\Exports\GPSOExport;
use App\Http\Controllers\Controller;
use App\Models\GpSusmissionLogError;
use App\Models\ListOrderGp;
use App\Models\ListOrderGpDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GPSubmissionController extends Controller
{
    public function index($list_id = null)
    {
        return view('spa.spa-index');
    }

    public function submissionList(Request $request)
    {
        $orderList = ListOrderGp::query()->orderBy('create_date', 'desc');
        $orders = $orderList->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function submissionListDetail(Request $request, $list_id)
    {
        $orderList = ListOrderGpDetail::leftJoin('order_list_by_genies', 'order_list_by_genies.id', '=', 'list_order_gp_details.ginee_order_id')->where('list_order_gp_id', $list_id)->select('list_order_gp_details.so_number', 'list_order_gp_details.ginee_order_id', 'list_order_gp_details.list_order_gp_id', 'order_list_by_genies.qty', 'order_list_by_genies.trx_id', 'order_list_by_genies.sku', 'order_list_by_genies.nominal', 'order_list_by_genies.pajak', 'order_list_by_genies.total_diskon', 'order_list_by_genies.nama_produk', 'order_list_by_genies.channel', 'order_list_by_genies.store', 'order_list_by_genies.biaya_komisi', 'order_list_by_genies.tanggal_transaksi')->selectRaw("SUM(tbl_order_list_by_genies.qty) as qty_total")->orderBy('order_list_by_genies.tanggal_transaksi', 'desc')->groupBy('order_list_by_genies.sku');
        $orders = $orderList->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => tap($orders)->map(function ($item) {
                $qty_total = $item->qty_total;
                $item['extended_prices'] = $item->extended_price;
                $item['freight_amounts'] = $item->freight_amount;
                $item['tax_amounts'] = $item->tax_amount;
                $item['total_discounts'] = $item->total_discount;
                $item['miscellaneouss'] = $item->miscellaneous;
                return $item;
            }),
            'errorLogs' => GpSusmissionLogError::where('list_order_gp_id', $list_id)->get()
        ]);
    }

    public function exportConvert($item_id)
    {
        return Excel::download(new GPSOExport($item_id), 'data-product-convert.xlsx');
    }
}
