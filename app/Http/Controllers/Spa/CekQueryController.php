<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\InventoryDetailItem;
use App\Models\InventoryItem;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\ProductVariantStock;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;

class CekQueryController extends Controller
{
    public function cekQuery(Request $request)
    {

        // purchase order
        $products = Product::all();
        $stock_movement = [];
        $warehouse_id = 4;
        foreach ($products as $key => $product) {
            // get pruduct variant ids
            $product_variant_ids = $product->variants()->pluck('product_variants.id')->toArray();

            // get begin stock
            $qty_begin_stock = (int) InventoryDetailItem::where('product_id', $product->id)->whereHas('inventoryStock', function ($query) use ($warehouse_id) {
                if ($warehouse_id) {
                    return $query->where('destination_warehouse_id', $warehouse_id)->where('inventory_type', 'transfer')->whereDate('created_at', '<', '2023-06-19');
                }
                return $query->where('inventory_type', 'transfer')->whereDate('created_at', '<', '2023-06-19');
            })->sum('qty');

            // get stock order
            $qty_order_lead_new = $this->getSalesOrder($product_variant_ids, [1], 'orderLead', $warehouse_id, 'sum');
            $qty_order_lead_open = $this->getSalesOrder($product_variant_ids, [2], 'orderLead', $warehouse_id, 'sum');
            $qty_order_lead_close = $this->getSalesOrder($product_variant_ids, [3], 'orderLead', $warehouse_id, 'sum');

            // count order lead
            $count_order_lead_new = $this->getSalesOrder($product_variant_ids, [1], 'orderLead', $warehouse_id, 'count');
            $count_order_lead_open = $this->getSalesOrder($product_variant_ids, [2], 'orderLead', $warehouse_id, 'count');
            $count_order_lead_close = $this->getSalesOrder($product_variant_ids, [3], 'orderLead', $warehouse_id, 'count');

            // order manual
            $qty_order_manual_new = $this->getSalesOrder($product_variant_ids, [1], 'orderManual', $warehouse_id, 'sum');
            $qty_order_manual_open = $this->getSalesOrder($product_variant_ids, [2], 'orderManual', $warehouse_id, 'sum');
            $qty_order_manual_close = $this->getSalesOrder($product_variant_ids, [3], 'orderManual', $warehouse_id, 'sum');

            // count order manual
            $count_order_manual_new = $this->getSalesOrder($product_variant_ids, [1], 'orderManual', $warehouse_id, 'count');
            $count_order_manual_open = $this->getSalesOrder($product_variant_ids, [2], 'orderManual', $warehouse_id, 'count');
            $count_order_manual_close = $this->getSalesOrder($product_variant_ids, [3], 'orderManual', $warehouse_id, 'count');


            // product return
            // $qty_product_return = (int) $product->inventoryItems()->where('type', 'return-prcved')->whereHas('inventoryReturn', function ($query) use ($warehouse_id) {
            //     if ($warehouse_id) {
            //         return $query->where('warehouse_id', $warehouse_id);
            //     }
            //     return $query;
            // })->where('received_vendor', 2)->sum('qty_diterima');

            // product return
            $qty_product_return = $this->getProductReturn($product_variant_ids, 2, 'return-prcved', $warehouse_id);

            // product return suplier
            $qty_product_return_suplier = $this->getProductReturn($product_variant_ids, 1, 'return-prcved', $warehouse_id);
            $stock_movement[] = [
                'no' => $key + 1,
                'product_name' => $product->name,
                'package' => $product->u_of_m,
                'sku' => $product?->sku ?? '-',
                'product_id' => $product->id,
                'qty_stock_master' => $product->final_stock,
                // 'product_variant_ids' => $product_variant_ids,
                'qty_begin_stock' => $qty_begin_stock,
                'order_lead' => [
                    'qty_new' => (int) $qty_order_lead_new,
                    'qty_open' => (int) $qty_order_lead_open,
                    'qty_close' => (int) $qty_order_lead_close,
                    'count_new' => (int) $count_order_lead_new,
                    'count_open' => (int) $count_order_lead_open,
                    'count_close' => (int) $count_order_lead_close,
                ],
                'order_manual' => [
                    'qty_new' => (int) $qty_order_manual_new,
                    'qty_open' => (int) $qty_order_manual_open,
                    'qty_close' => (int) $qty_order_manual_close,
                    'count_new' => (int) $count_order_manual_new,
                    'count_open' => (int) $count_order_manual_open,
                    'count_close' => (int) $count_order_manual_close,
                ],
                'qty_product_return' => (int) $qty_product_return,
                'qty_product_return_suplier' => (int) $qty_product_return_suplier,
            ];
        }

        return response()->json($stock_movement);
    }

    public function getSalesOrder($variant_ids = [], $status = [], $join = 'orderLead', $warehouse_id = null, $type = 'sum')
    {
        $order = ProductNeed::whereIn('product_id', $variant_ids)->whereHas($join, function ($query) use ($status, $join, $warehouse_id) {
            $table = $join == 'orderManual' ? 'order_manuals' : 'order_leads';
            if ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id)->whereIn($table . '.status', $status)->whereDate('created_at', '<', '2023-06-19');
            }
            return $query->whereIn($table . '.status', $status)->whereDate('created_at', '<', '2023-06-19');
        });

        if ($type == 'count') {
            return  $order->count();
        }

        return $order->sum('qty');
    }

    public function getProductReturn($variant_ids = [], $received_vendor = 1, $type = 'stock', $warehouse_id = null, $field = 'qty_diterima')
    {
        $qty = InventoryItem::whereIn('product_id', $variant_ids)->where('type', $type)->whereHas('inventoryReturn', function ($query) use ($warehouse_id) {
            if ($warehouse_id) {
                return $query->whereDate('created_at', '<', '2023-06-19')->where('warehouse_id', $warehouse_id);
            }
            return $query->whereDate('created_at', '<', '2023-06-19');
        })->where('received_vendor', $received_vendor)->sum($field);

        return $qty;
    }
}
