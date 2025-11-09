<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariantBundlingStock;
use App\Models\Warehouse;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromQuery;
// use Maatwebsite\Excel\Concerns\WithColumnFormatting;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

// use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductMasterExport implements FromView, WithColumnWidths
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
        $warehouse_id = $request->warehouse_ids ?? ['all'];
        $account_id = $request->account_id ?? 1;
        $wh_ids = [];

        $product =  Product::query();
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $product->whereIn('status', $status);
        }

        if (is_array($warehouse_id)) {
            $wh_ids = $warehouse_id;
            if (in_array('all', $warehouse_id)) {
                $wh_ids = Warehouse::pluck('id')->toArray();
            }
        }

        $products = $product->orderBy('created_at', 'asc')->whereNull('deleted_at')->get();

        $productMaster = tap($products)->map(function ($product) use ($wh_ids, $account_id) {
            $stock_warehouse = ProductStock::where('product_id', $product->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id)->sum('stock');
            $stock_bundling_warehouse = ProductVariantBundlingStock::whereHas('bundling', function ($query) use ($wh_ids, $product) {
                $query->where('product_id', $product->id);
            })->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id)->orderBy('qty', 'asc')->first(['qty']);
            $product->stock_by_warehouse = $stock_warehouse > 0 ? $stock_warehouse : 0;
            $product->stock_bundling_warehouse = $stock_bundling_warehouse ? $stock_bundling_warehouse->qty : 0;

            return $product;
        });

        $pm_data = [];

        foreach ($productMaster as $key => $value) {
            // merge value same value
            $pm_data[$key]['id']       = $value->id;
            $pm_data[$key]['name']     = $value?->name;
            $pm_data[$key]['sku']      = $value?->sku;
            $pm_data[$key]['weight']   = $value->weight;
            $pm_data[$key]['status']   = $value?->status == 1 ? 'Active' : 'Non Active';
            $pm_data[$key]['description']  = $value->description;
            $pm_data[$key]['stock_warehouse'] = $value->stock_warehouse;
        }
        return view('export.product-master', [
            'data' => $pm_data,
        ]);
    }

    public function title(): string
    {
        return $this->title;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, // ID column width
            'B' => 5, // Name column width
            'C' => 50, // Email column width
            'D' => 30, // Created At column width
            'E' => 5, // Updated At column width
            'F' => 10, // Updated At column width
            'G' => 150, // Updated At column width
            'H' => 40, // Updated At column width
            'I' => 15, // Updated At column width
        ];
    }
}
