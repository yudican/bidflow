<?php

namespace App\Exports;

use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantStock;
use App\Models\Role;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class ProductVariantExport implements FromView, ShouldAutoSize
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
        $role_id = $request->role_id;
        $sku = $request->sku;
        $product_id = $request->product_id;
        $sales_channel = $request->sales_channel;
        $warehouse_id = $request->warehouse_ids;
        $account_id = $request->account_id;
        $wh_ids = [$request->warehouse_id];

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

        if (is_array($warehouse_id)) {
            $wh_ids = $warehouse_id;
            if (in_array('all', $warehouse_id)) {
                $wh_ids = Warehouse::pluck('id')->toArray();
            }
        }

        $products = $product->orderBy('created_at', 'asc')->whereNull('deleted_at')->get();
        if ($role_id) {
            $role = Role::find($role_id);
            $productMaster = tap($products)->map(function ($item) use ($role, $wh_ids, $account_id) {
                $item['final_price'] = $item->getPrice($role->role_type)['final_price'];
                if ($item->is_bundling > 0) {
                    $warehouse = collect($item->stock_warehouse);
                    // Find the warehouse with ID 4

                    if ($warehouse->isNotEmpty()) {
                        // Sum the stock for the product_id
                        $totalStock = $warehouse->sum('stock');

                        // Add total stock and stock_of_market to the product
                        $item['stocks'] = $totalStock;
                        $item['stock_of_market'] = $totalStock > 0 ? floor($totalStock / $item->qty_bundling) : 0;

                        return $item;
                    }
                    $item['stocks'] = 0;
                    $item['stock_of_market'] = 0;
                    return $item;
                }

                $stock_warehouse = ProductVariantStock::where('product_variant_id', $item->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id);
                $item['stocks'] = $stock_warehouse->sum('qty') > 0 ? $stock_warehouse->sum('qty') : 0;
                $item['stock_of_market'] = $stock_warehouse->sum('stock_of_market') > 0 ? $stock_warehouse->sum('stock_of_market') : 0;

                return $item;
            });

            $pm_data = [];

            foreach ($productMaster as $key => $value) {
                // merge value same value
                $pm_data[$key]['no']       = $key + 1;
                $pm_data[$key]['master_name']     = $value->product?->name;
                $pm_data[$key]['product_name']      = $value?->name;
                $pm_data[$key]['package_name']   = $value->package_name;
                $pm_data[$key]['variant_name']   = $value?->variant_name;
                $pm_data[$key]['sku']  = $value->sku;
                $pm_data[$key]['sku_variant'] = $value->sku_variant;
            }
            return view('export.product-variant', [
                'data' => $pm_data,
            ]);
        }

        $productMaster = tap($products)->map(function ($product) use ($wh_ids, $account_id) {
            if ($product->is_bundling > 0) {
                $warehouse = collect($product->stock_warehouse);
                // Find the warehouse with ID 4

                if ($warehouse->isNotEmpty()) {
                    // Sum the stock for the product_id
                    $totalStock = $warehouse->sum('stock');

                    // Add total stock and stock_of_market to the product
                    $product['stocks'] = $totalStock;
                    $product['stock_of_market'] = $totalStock > 0 ? floor($totalStock / $product->qty_bundling) : 0;

                    return $product;
                }
                $product['stocks'] = 0;
                $product['stock_of_market'] = 0;
                return $product;
            }

            $stock_warehouse = ProductVariantStock::where('product_variant_id', $product->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id);
            $product['stocks'] = $stock_warehouse->sum('qty') > 0 ? $stock_warehouse->sum('qty') : 0;
            $product['stock_of_market'] = $stock_warehouse->sum('stock_of_market') > 0 ? $stock_warehouse->sum('stock_of_market') : 0;
            return $product;
        });

        $pm_data = [];

        foreach ($productMaster as $key => $value) {
            // merge value same value
            $pm_data[$key]['no']       = $key + 1;
            $pm_data[$key]['master_name']     = $value->product?->name;
            $pm_data[$key]['product_name']      = $value?->name;
            $pm_data[$key]['package_name']   = $value->package_name;
            $pm_data[$key]['variant_name']   = $value?->variant_name;
            $pm_data[$key]['sku']  = $value->sku;
            $pm_data[$key]['sku_variant'] = $value->sku_variant;
        }
        return view('export.product-variant', [
            'data' => $pm_data,
        ]);
    }

    public function title(): string
    {
        return $this->title;
    }
}
