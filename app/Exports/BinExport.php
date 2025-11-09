<?php

namespace App\Exports;

use App\Models\MasterBin;
use App\Models\MasterBinStock;
use App\Models\ProductVariant;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BinExport implements FromView, ShouldAutoSize
{
    protected $request;
    protected $title;
    public function __construct($request, $title = 'ExportConverData')
    {
        $this->request = $request;
        $this->title = $title;
    }

    public function view(): View
    {
        $request = $this->request;
        $search = $request->search;
        $status = $request->status;
        $package_id = $request->package_id;
        $variant_id = $request->variant_id;
        $sku = $request->sku;
        $product_id = $request->product_id;
        $sales_channel = $request->sales_channel;
        $master_bin_id = $request->master_bin_id;

        $product =  ProductVariant::query();
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $product->whereIn('status', $status);
        }

        if ($package_id) {
            $product->whereIn('package_id', $package_id);
        }

        if ($variant_id) {
            $product->whereIn('variant_id', $variant_id);
        }

        if ($sku) {
            $product->whereIn('sku', $sku);
        }

        if ($product_id) {
            $product->where('product_id', $product_id);
        }

        if ($sales_channel) {
            $product->where('sales_channel', 'like', "%$sales_channel%");
        }

        if (is_array($master_bin_id)) {
            if (in_array('all', $master_bin_id)) {
                $master_bin_id = MasterBin::pluck('id')->toArray();
            }
        }

        $products = $product->orderBy('created_at', 'asc')->whereNull('deleted_at')->get();

        $new_products = tap($products)->map(function ($product) use ($master_bin_id) {
            $stock_master_bin = MasterBinStock::where('product_variant_id', $product->id);
            if ($master_bin_id) {
                if (is_array($master_bin_id)) {
                    $stock_master_bin->whereIn('master_bin_id', $master_bin_id);
                } else {
                    $stock_master_bin->where('master_bin_id', $master_bin_id);
                }
            }
            $product['stocks'] = $stock_master_bin->sum('stock') > 0 ? $stock_master_bin->sum('stock') : 0;

            return $product;
        });

        $bin_data = [];
        foreach ($new_products as $key => $value) {
            // merge value same value
            $bin_data[$key]['no']             = $key + 1;
            $bin_data[$key]['product_name']   = $value?->name;
            $bin_data[$key]['package_name']   = $value?->package_name ?? '-';
            $bin_data[$key]['stock']          = $value->stocks;
            $bin_data[$key]['sales_channel']  = $value?->sales_channel;
            $bin_data[$key]['variant_name']   = $value->variant_name;
            $bin_data[$key]['price']          = $value?->price['final_price'];
        }
        return view('export.bin', [
            'data' => $bin_data,
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
