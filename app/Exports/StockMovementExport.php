<?php

namespace App\Exports;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StockMovementExport implements FromView, ShouldAutoSize
{
    protected $request = [];
    protected $title = null;

    public function __construct($request = [], $title = 'Stock Movement List')
    {
        $this->request = $request;
        $this->title = $title;
    }

    public function view(): View
    {

        $request = $this->request;
        $products = Product::all();
        $stock_movement = [];
        foreach ($products as $key => $product) {
            $qty_order_lead =  ProductNeed::whereIn('product_id', $product->variants()->pluck('id')->toArray())->whereHas('orderLead', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->whereIn('order_leads.status', [1, 2, 3])->whereBetween(
                            'order_leads.assign_date',
                            $request['tanggal_transaksi']
                        )->where('warehouse_id', $request['warehouse_id']);
                    }
                    return $query->whereIn('order_leads.status', [1, 2, 3])->whereBetween(
                        'order_leads.assign_date',
                        $request['tanggal_transaksi']
                    );
                } else {
                    if ($request['warehouse_id']) {
                        return $query->whereIn('order_leads.status', [1, 2, 3])->whereDate('order_leads.assign_date', '>=', '2023-08-20')->where('warehouse_id', $request['warehouse_id']);
                    }
                    return $query->whereIn('order_leads.status', [1, 2, 3])->whereDate('order_leads.assign_date', '>=', '2023-08-20');
                }
            })->sum('qty');

            $qty_order_manual =  ProductNeed::whereIn('product_id', $product->variants()->pluck('id')->toArray())->whereHas('orderManual', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->whereIn('order_manuals.status', [1, 2, 3])->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'order_manuals.assign_date',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->whereIn('order_manuals.status', [1, 2, 3])->whereBetween(
                        'order_manuals.assign_date',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->whereIn('order_manuals.status', [1, 2, 3])->where('warehouse_id', $request['warehouse_id'])->whereDate('order_manuals.assign_date', '>=', '2023-08-20');
                }
                return $query->whereIn('order_manuals.status', [1, 2, 3])->whereDate('order_manuals.assign_date', '>=', '2023-08-20');
            })->sum('qty');

            // qty order 
            $qty_order_lead2 =  ProductNeed::whereIn('product_id', $product->variants()->pluck('id')->toArray())->whereHas('orderLead', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->whereIn('order_leads.status', [2, 3])->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'order_leads.assign_date',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->whereIn('order_leads.status', [2, 3])->whereBetween(
                        'order_leads.assign_date',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->whereIn('order_leads.status', [2, 3])->where('warehouse_id', $request['warehouse_id'])->whereDate('order_leads.assign_date', '>=', '2023-08-20');
                }
                return $query->whereIn('order_leads.status', [2, 3])->whereDate('order_leads.assign_date', '>=', '2023-08-20');
            });

            $qty_order_manual2 = ProductNeed::whereHas('orderManual', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->whereIn('order_manuals.status', [2, 3])->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'order_manuals.assign_date',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->whereIn('order_manuals.status', [2, 3])->whereBetween(
                        'order_manuals.assign_date',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->whereIn('order_manuals.status', [2, 3])->where('warehouse_id', $request['warehouse_id'])->whereDate('order_manuals.assign_date', '>=', '2023-08-20');
                }
                return $query->whereIn('order_manuals.status', [2, 3])->whereDate('order_manuals.assign_date', '>=', '2023-08-20');
            })->whereIn('product_id', $product->variants()->pluck('id'));


            $qty_begin_stock = (int) $product->inventoryItems()->whereHas('inventoryStock', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->whereNotNull('reference_number')->whereInventoryStatus('alocated')->whereBetween(
                            'inventory_product_stocks.created_at',
                            $request['tanggal_transaksi']
                        )->where('warehouse_id', $request['warehouse_id']);
                    }
                    return $query->whereNotNull('reference_number')->whereInventoryStatus('alocated')->whereBetween(
                        'inventory_product_stocks.created_at',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->whereNotNull('reference_number')->whereInventoryStatus('alocated')->where('warehouse_id', $request['warehouse_id'])->whereDate('inventory_product_stocks.created_at', '>=', '2023-08-20');
                }
                return $query->whereNotNull('reference_number')->whereInventoryStatus('alocated')->whereDate('inventory_product_stocks.created_at', '>=', '2023-08-20');
            })->whereType('stock')->sum('qty');

            // qty_delivered
            $qty_delivered = (int) PurchaseOrderItem::whereHas('purchaseOrder', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->where('type_po', 'product')->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'purchase_orders.created_at',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->where('type_po', 'product')->whereBetween(
                        'purchase_orders.created_at',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->where('type_po', 'product')->where('warehouse_id', $request['warehouse_id'])->whereDate('purchase_orders.created_at', '>=', '2023-08-20');
                }
                return $query->where('type_po', 'product')->whereDate('purchase_orders.created_at', '>=', '2023-08-20');
            })->where('product_id', $product->id)->groupBy(['purchase_order_id', 'product_id'])->sum('qty_diterima');

            // $qty_delivered2 = (int) PurchaseOrderItem::whereHas('purchaseOrder', function ($query) {
            //     return $query->where('type_po', 'product')->where('status', 2);
            // })->whereNotNull('received_number')->where('product_id', $product->id)->whereDate('purchase_order_items.created_at', '>=', '2023-08-20')->sum('qty_diterima');

            // qty_product_return
            $qty_product_return = (int) $product->inventoryItems()->where('type', 'return-prcved')->whereHas('inventoryReturn', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'inventory_product_returns.created_at',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->whereBetween(
                        'inventory_product_returns.created_at',
                        $request['tanggal_transaksi']
                    );
                }

                if ($request['warehouse_id']) {
                    return $query->where('warehouse_id', $request['warehouse_id'])->whereDate('inventory_product_returns.created_at', '>=', '2023-08-20');
                }
                return $query->whereDate('inventory_product_returns.created_at', '>=', '2023-08-20');
            })->where('received_vendor', 2)->sum('qty_diterima');

            $qty_return_suplier = (int) $product->inventoryItems()->whereHas('inventoryReturn', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'inventory_product_returns.created_at',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->whereBetween(
                        'inventory_product_returns.created_at',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->where('warehouse_id', $request['warehouse_id'])->whereDate('inventory_product_returns.created_at', '>=', '2023-08-20');
                }
                return $query->whereDate('inventory_product_returns.created_at', '>=', '2023-08-20');
            })->where('type', 'return-prcved')->where('received_vendor', 1)->sum('qty_diterima');

            // qty_sales_return

            $qty_product_transfer = (int) $product->inventoryDetailItems()->whereHas('inventoryStock', function ($query) use ($request) {
                if ($request['tanggal_transaksi']) {
                    if ($request['warehouse_id']) {
                        return $query->where('warehouse_id', $request['warehouse_id'])->whereBetween(
                            'inventory_product_stocks.created_at',
                            $request['tanggal_transaksi']
                        );
                    }
                    return $query->whereBetween(
                        'inventory_product_stocks.created_at',
                        $request['tanggal_transaksi']
                    );
                }
                if ($request['warehouse_id']) {
                    return $query->where('warehouse_id', $request['warehouse_id'])->whereDate('inventory_product_stocks.created_at', '>=', '2023-08-20');
                }
                return $query->whereDate('inventory_product_stocks.created_at', '>=', '2023-08-20');
            })->sum('qty_alocation');

            // qty_sales
            $qty_stock_order = $qty_order_lead + $qty_order_manual;
            $qty_sales_order = $qty_order_lead2->sum('qty') + $qty_order_manual2->sum('qty');
            $new_begin_stock = 0;
            if ($qty_order_lead2->first() || $qty_order_manual2->first()) {
                $new_begin_stock = $qty_begin_stock;
            }
            $stock_movement[] = [
                'product_name' => $product->name,
                'uom' => $product->u_of_m,
                'sku' => $product?->sku ?? '-',
                'product_id' => $product->id,
                'qty_begin_stock' => $qty_begin_stock,
                'qty_delivered' => $qty_delivered,
                'qty_sales_return' => $qty_product_return,
                // 'qty_sales_return' => $qty_sales_return,
                'qty_return_suplier' => $qty_return_suplier,
                'qty_stock' => $qty_stock_order,
                'qty_sales' => $qty_sales_order,
                'qty_transfer_in' => 0,
                'qty_begin_stock2' => $qty_begin_stock,
                'qty_order_lead2' => $qty_order_lead2,
                'varisnts' => $product->variants()->pluck('id')->toArray(),
                'qty_order_manual2' => $qty_order_manual2,
                'qty_transfer_out' => $qty_product_transfer,
                'qty_end_stock' => ($new_begin_stock + $qty_product_return) - $qty_sales_order,
                'qty_end_forecast' => ($qty_begin_stock + $qty_return_suplier) - $qty_stock_order,
            ];
        }

        return view('export.stock-movement', [
            'data' => $stock_movement,
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
