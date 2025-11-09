<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Exports\StockMovementExport;
use App\Models\OrderDelivery;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\Paginator;

class StockMovementController extends Controller
{
    public function index($purchase_order_id = null)
    {
        return view('spa.spa-index');
    }

    public function listStockMovementOld(Request $request)
    {
        $search = $request->search;
        $warehouse_id = $request->warehouse_id;
        $date = $request->tanggal_transaksi;
        $account_id = $request->account_id;
        $order =  PurchaseOrder::query();

        if ($warehouse_id) {
            $where_warehouse = " and warehouse_id = " . $warehouse_id;
        } else {
            $where_warehouse = "";
        }

        if ($date) {
            $where_create = " and date(po.created_at) between " . $date[0] . " and " . $date[1];
            $where_received = " and date(received_date) between " . $date[0] . " and " . $date[1];
            $where_create2 = " and date(i.created_at) between " . $date[0] . " and " . $date[1];
        } else {
            $where_create = "";
            $where_received = "";
            $where_create2 = "";
        }

        $data = DB::select(DB::raw("select pr.id, pr.sku, pr.`name` as product_name, p.`name` as package_name, b.`name` as brand,
                (SELECT SUM(po.qty_diterima) FROM tbl_purchase_order_items po LEFT JOIN tbl_purchase_orders por on po.purchase_order_id = por.id WHERE po.product_id = pr.id and por.type_po='product'" . $where_create . $where_warehouse . ") as begin_stock,
                (SELECT SUM(po.qty) FROM tbl_purchase_order_items po LEFT JOIN tbl_purchase_orders poi on poi.id = po.purchase_order_id WHERE poi.status = 2 and po.product_id = pr.id" . $where_create . ") as purchase_delivered,
                (SELECT SUM(i.qty_diterima) FROM tbl_inventory_items i INNER JOIN tbl_inventory_product_returns ir on ir.uid_inventory = i.uid_inventory INNER JOIN tbl_product_variants v on i.product_id = v.id WHERE v.product_id = pr.id" . $where_received . ") as product_return,
                (SELECT SUM(i.qty_diterima) FROM tbl_inventory_items i INNER JOIN tbl_inventory_product_returns ir on ir.uid_inventory = i.uid_inventory INNER JOIN tbl_product_variants v on i.product_id = v.id WHERE v.product_id = pr.id and i.type = 'return-received'" . $where_received . ") as sales_return,
                (SELECT SUM(i.qty) FROM tbl_inventory_detail_items i INNER JOIN tbl_product_variants v on i.product_id = v.id LEFT JOIN tbl_inventory_product_stocks ips on i.uid_inventory = ips.uid_inventory WHERE ips.inventory_status = 'allocate' and v.product_id = pr.id" . $where_received . ") as transfer_in,
                (SELECT SUM(i.qty) FROM tbl_product_needs i INNER JOIN tbl_product_variants v on i.product_id = v.id WHERE v.product_id = pr.id and i.status = 1" . $where_create2 . ") as stock,
                (SELECT SUM(i.qty) FROM tbl_inventory_items i LEFT JOIN tbl_inventory_product_stocks ips on i.uid_inventory = ips.uid_inventory INNER JOIN tbl_product_variants v on i.product_id = v.id WHERE v.product_id = pr.id and i.received_vendor = 1" . $where_received . ") as return_suplier,
                (SELECT SUM(i.qty) FROM tbl_product_needs i INNER JOIN tbl_product_variants v on i.product_id = v.id WHERE v.product_id = pr.id" . $where_create2 . ") as sales,
                (SELECT SUM(i.qty) FROM tbl_inventory_items i INNER JOIN tbl_product_variants v on i.product_id = v.id LEFT JOIN tbl_inventory_product_stocks ips on i.uid_inventory = ips.uid_inventory WHERE ips.inventory_type = 'transfer' and v.product_id = pr.id" . $where_warehouse . $where_received . ") as transfer_out
                From tbl_products pr
                left join tbl_sku_masters s on pr.sku = s.sku
                left join tbl_packages p on s.package_id = p.id
                left join tbl_brands b on b.id = pr.brand_id"));

        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->where('po_number', 'like', "%$search%");
                $query->orWhere('vendor_code', 'like', "%$search%");
                $query->orWhereHas('createdBy', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            });
        }

        // cek switch account
        if ($account_id) {
            $order->where('company_id', $account_id);
        }

        // print_r(count($data));die();

        // $orders = $order->orderBy('created_at', 'desc')->paginate($request->perpage);
        // $stocks = new Paginator($data, 10, count($data));

        $page = 1;
        $perPage = 10;
        $query = $data;
        $currentPage = $request->get('page', 1) - 1;
        $pagedData = array_slice($query, $currentPage * $perPage, $perPage);
        $stocks =  new Paginator($pagedData, count($query), $perPage);
        // print_r($stocks);die();
        return response()->json([
            'status' => 'success',
            'data' => $stocks,
            'message' => 'List Stock Movement'
        ]);
    }

    public function listStockMovementOld2(Request $request)
    {
        $products = Product::all();
        $stock_movement = [];
        foreach ($products as $key => $product) {
            $variantsIds = $product->variants()->pluck('id')->toArray();
            $masterId = $product->id;
            // qty_stock_order
            $qty_stock_order = OrderDelivery::where('status', '!=', 'cancel')->whereHas('productNeed', function ($queryProductNeed) use ($variantsIds, $request) {
                return $queryProductNeed->whereIn('product_id', $variantsIds)
                    ->where(function ($query) use ($request) {
                        $query->whereHas('orderLead', function ($subquery) use ($request) {
                            $this->applyOrderConditions($subquery, 'order_leads', $request, [1, 2, 3]);
                        });

                        $query->orWhereHas('orderManual', function ($subquery) use ($request) {
                            $this->applyOrderConditions($subquery, 'order_manuals', $request, [1, 2, 3], ['manual', 'freebies']);
                        });
                    });
            })->sum('qty_delivered');

            // qty_sales_order
            $qty_sales_order = OrderDelivery::where('status', '!=', 'cancel')->whereHas('productNeed', function ($queryProductNeed) use ($variantsIds, $request) {
                return $queryProductNeed->whereIn('product_id', $variantsIds)
                    ->where(function ($query) use ($request) {
                        $query->whereHas('orderLead', function ($subquery) use ($request) {
                            $this->applyOrderConditions($subquery, 'order_leads', $request, [2, 3]);
                        });

                        $query->orWhereHas('orderManual', function ($subquery) use ($request) {
                            $this->applyOrderConditions($subquery, 'order_manuals', $request, [2, 3], ['manual', 'freebies']);
                        });
                    });
            })->sum('qty_delivered');

            // qty_sales_order_konsinyasi
            $qty_sales_order_konsinyasi = OrderDelivery::where('status', '!=', 'cancel')->whereHas('productNeed', function ($queryProductNeed) use ($variantsIds, $request) {
                $queryProductNeed->whereIn('product_id', $variantsIds)
                    ->where(function ($query) use ($request) {
                        $query->whereHas('orderLead', function ($subquery) use ($request) {
                            $this->applyOrderConditions($subquery, 'order_leads', $request, [2, 3]);
                        });
                        $query->orWhereHas('orderManual', function ($subquery) use ($request) {
                            $this->applyOrderConditions($subquery, 'order_manuals', $request, [2, 3], ['konsinyasi']);
                        });
                    });
            })->sum('qty_delivered');

            $qty_begin_stock = (int) $product->inventoryItems()->whereHas('inventoryStock', function ($query) use ($request) {
                $query->whereNotNull('reference_number')
                    ->whereInventoryStatus('alocated');
                // ->whereType('stock');

                if ($request->tanggal_transaksi) {
                    $query->whereBetween('inventory_product_stocks.created_at', $request->tanggal_transaksi);
                } else {
                    $query->whereDate('inventory_product_stocks.created_at', '>=', '2023-08-20');
                }

                if ($request->warehouse_id) {
                    $query->where('warehouse_id', $request->warehouse_id);
                }
            })->sum('qty');

            // qty_delivered
            $qty_delivered = (int) PurchaseOrderItem::whereHas('purchaseOrder', function ($query) use ($request) {
                $query->where('type_po', 'product')
                    ->when($request->warehouse_id, function ($query) use ($request) {
                        $query->where('warehouse_id', $request->warehouse_id);
                    })
                    ->when($request->tanggal_transaksi, function ($query) use ($request) {
                        $query->whereBetween('purchase_orders.created_at', $request->tanggal_transaksi);
                    }, function ($query) {
                        $query->whereDate('purchase_orders.created_at', '>=', '2023-08-20');
                    });
            })
                ->where('product_id', $product->id)
                ->groupBy(['purchase_order_id', 'product_id'])
                ->sum('qty_diterima');

            $qty_product_return = $this->getInventoryReturnSum($product, $request->warehouse_id, $request->tanggal_transaksi, 2);

            $qty_return_suplier = $this->getInventoryReturnSum($product, $request->warehouse_id, $request->tanggal_transaksi, 1);

            // qty_sales_return
            $qty_product_transfer_in = $this->getInventorySum($product, $request->warehouse_id, $request->tanggal_transaksi, 'destination_warehouse_id');
            $qty_product_transfer = $this->getInventorySum($product, $request->warehouse_id, $request->tanggal_transaksi, 'warehouse_id');

            // inventory bin transfer
            $qty_product_transfer_in_bin = $this->getInventorySum($product, $request->warehouse_id, $request->tanggal_transaksi, 'master_bin_id', 'konsinyasi');

            // qty_sales
            $new_begin_stock = $qty_begin_stock;


            if ($request->tanggal_transaksi) {
                $new_begin_stock = $product->final_stock;
                $qty_begin_stock = $product->final_stock;

                if ($request->warehouse_id) {
                    $collection = collect($product->stock_warehouse);
                    // Find the warehouse with ID 4
                    $warehouse = $collection->where('id', $request->warehouse_id)->first();
                    $new_begin_stock = intval($warehouse['stock']);
                    $qty_begin_stock = intval($warehouse['stock']);
                }
            }

            // $qty_begin_stock_bin = 

            $stock_movement[] = [
                'product_name' => $product->name,
                'uom' => $product->u_of_m,
                'sku' => $product?->sku ?? '-',
                'product_id' => $product->id,
                'qty_begin_stock' => $qty_begin_stock,
                // 'qty_begin_stock_bin' => $qty_begin_stock_bin,
                'qty_delivered' => $qty_delivered,
                'qty_sales_return' => $qty_product_return,
                // 'qty_sales_return' => $qty_sales_return,
                'qty_return_suplier' => $qty_return_suplier,
                'qty_stock' => $qty_stock_order,
                'qty_sales' => $qty_sales_order,
                'qty_sales_konsinyasi' => $qty_sales_order_konsinyasi,
                'varisnts' => $product->variants()->pluck('id')->toArray(),
                'qty_transfer_out' => $qty_product_transfer,
                'qty_transfer_in' => $qty_product_transfer_in,
                'qty_product_transfer_in_bin' => $qty_product_transfer_in_bin,
                'qty_end_stock' => ($new_begin_stock + $qty_product_transfer_in + $qty_product_return) - $qty_sales_order,
                'qty_end_forecast' => ($qty_begin_stock + $qty_product_transfer_in + $qty_return_suplier) - $qty_stock_order,
            ];
        }

        $perPage = 30;
        $query = $stock_movement;
        $currentPage = $request->page - 1;
        $pagedData = array_slice($query, $currentPage * $perPage, $perPage);
        $stocks =  new Paginator($pagedData, count($query), $perPage);
        return response()->json([
            'status' => 'success',
            'data' => $stocks,
            'message' => 'List Stock Movement'
        ]);
    }

    public function listStockMovement(Request $request)
    {
        $tanggal_transaksi = $request->tanggal_transaksi;
        $stock = DB::table('stock_movements as a')
            ->join('products as p', 'a.product_id', '=', 'p.id')
            ->select(
                'a.id',
                'p.name as product_name',
                'a.sku',
                DB::raw('SUM(tbl_a.begin_stock) as begin_stock'),
                DB::raw('SUM(tbl_a.in_purchase_order) as in_purchase_order'),
                DB::raw('SUM(tbl_a.in_transfer) as in_transfer'),
                DB::raw('SUM(tbl_a.in_sales_return) as in_sales_return'),
                DB::raw('SUM(tbl_a.out_sales_order) as out_sales_order'),
                DB::raw('SUM(tbl_a.out_transfer) as out_transfer'),
                DB::raw('SUM(tbl_a.out_return_suplier) as out_return_suplier')
            )
            ->where('company_id', auth()->user()->company_id);

        if ($tanggal_transaksi) {
            $startDate = $tanggal_transaksi[0];
            $endDate = $tanggal_transaksi[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $stock->whereBetween('a.created_at', [$startDate, $endDate]);
        } else {
            $stock->whereDate('a.created_at', Carbon::now());
        }

        if ($request->warehouse_id) {
            $stock->where('a.warehouse_id', $request->warehouse_id);
        }

        $stocks = $stock->orderBy('a.created_at')->groupBy('a.warehouse_id')->groupBy('a.product_id')->paginate(50);
        return response()->json([
            'status' => 'success',
            'data' => $stocks,
            'message' => 'List Stock Movement'
        ]);
    }

    public function export(Request $request)
    {

        $file_name = 'convert/FIS-Stock_Movement-' . date('d-m-Y') . '.xlsx';

        Excel::store(new StockMovementExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function applyOrderConditions($query, $orderType, $request, $status = [1, 2, 3])
    {
        $query->whereIn("$orderType.status", $status);

        if ($request->tanggal_transaksi) {
            $query->whereBetween("$orderType.assign_date", $request->tanggal_transaksi);
        }

        if ($request->warehouse_id) {
            $query->where("$orderType.warehouse_id", $request->warehouse_id);
        }
    }

    public function getInventorySum($product, $warehouseId, $tanggalTransaksi, $warehouse, $inventoryType = 'transfer')
    {
        return (int) $product->inventoryDetailItems()->whereHas('inventoryStock', function ($query) use ($warehouseId, $tanggalTransaksi, $inventoryType, $warehouse) {
            $query->where('inventory_type', $inventoryType)
                ->when($warehouseId, function ($query) use ($warehouseId, $warehouse) {
                    $query->where($warehouse, $warehouseId);
                })
                ->when($tanggalTransaksi, function ($query) use ($tanggalTransaksi) {
                    $query->whereBetween('inventory_product_stocks.created_at', $tanggalTransaksi);
                }, function ($query) {
                    $query->whereDate('inventory_product_stocks.created_at', '>=', '2023-08-20');
                });
        })->sum('qty_alocation');
    }

    function getInventoryReturnSum($product, $warehouseId, $tanggalTransaksi, $receivedVendor)
    {
        return (int) $product->inventoryItems()
            ->where('type', 'return-prcved')
            ->whereHas('inventoryReturn', function ($query) use ($warehouseId, $tanggalTransaksi) {
                $query->when($warehouseId, function ($query) use ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                })
                    ->when($tanggalTransaksi, function ($query) use ($tanggalTransaksi) {
                        $query->whereBetween('inventory_product_returns.created_at', $tanggalTransaksi);
                    }, function ($query) {
                        $query->whereDate('inventory_product_returns.created_at', '>=', '2023-08-20');
                    });
            })
            ->where('received_vendor', $receivedVendor)
            ->sum('qty_diterima');
    }
}
