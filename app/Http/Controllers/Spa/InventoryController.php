<?php

namespace App\Http\Controllers\Spa;

use App\Exports\Template\ImportKonsinyasiTemplate;
use App\Jobs\ImportTransferKonsinyasiQueue;
use App\Jobs\ImportSalesOrderUploadQueue;
use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Exports\ProductTransferExport;
use App\Exports\ProductReceivedExport;
use App\Exports\ProductReturnExport;
use App\Jobs\UpdatePriceQueue;
use App\Models\CompanyAccount;
use App\Models\CompanyUser;
use App\Models\InventoryDetailItem;
use App\Models\InventoryItem;
use App\Models\InventoryProductReturn;
use App\Models\InventoryProductStock;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantBundlingStock;
use App\Models\ProductVariantStock;
use App\Models\PurchaseOrderItem;
use App\Models\StockAllocationHistory;
use App\Models\Warehouse;
use App\Models\OrderTransfer;
use App\Models\MasterBinStock;
use App\Models\OrderManual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    public function index($inventory_id = null)
    {
        return view('spa.spa-index');
    }

    public function getProductCount()
    {
        $company_id = auth()->user()->company_id;
        $data = [
            [
                'title' => 'STOCK PRODUCT RECEIVED',
                'value' => InventoryProductStock::where('inventory_type', 'received')->where('company_id', $company_id)->count(),
                'path' => 'inventory-product-stock',
                'color' => '[#FFC120]',
            ],
            [
                'title' => 'STOCK PRODUCT TRANSFER',
                'value' => InventoryProductStock::where('inventory_type', 'transfer')->where('company_id', $company_id)->count(),
                'path' => 'inventory-product-transfer',
                'color' => 'blueColor',
            ],
            [
                'title' => 'PRODUK RETURN',
                'value' => InventoryProductReturn::where('company_id', $company_id)->count(),
                'path' => 'inventory-product-return',
                'color' => '[#FE8311]',
            ],
            [
                'title' => 'STOCK PRODUCT KONSINYASI',
                'value' => InventoryProductStock::where('inventory_type', 'konsinyasi')->where('company_id', $company_id)->count(),
                'path' => 'item-transfer-konsinyasi',
                'color' => 'blueColor',
            ],
        ];
        return response()->json($data);
    }

    public function getInfoCreated()
    {
        return response()->json([
            'created_by_name' => auth()->user()->name,
            'created_on' => date('Y-m-d'),
            'nomor_sr' => $this->generatePrNumber(),
        ]);
    }

    public function orderKonsinyasiTemplate(Request $request)
    {
        $orderItems = DB::table('inventory_detail_items as b')
            ->select(
                'b.master_bin_id as id',
                'od.order_number',
                'uc.uid',
                'u.name as created_by_name',
                'pt.name as payment_term_name',
                'a.created_at',
                'p.name as product_name',
                'bin.name as bin_name',
                'bin.location as bin_location',
                'b.product_id',
                'b.qty',
                DB::raw('tbl_b.price_nego/tbl_b.qty as price_nego'), // Added alias for the calculation
                'b.discount',
                'b.from_warehouse_id',
                'b.tax_id',

                'b.master_bin_id',
                'od.total as nominal'
            )
            ->join('inventory_product_stocks as a', 'b.uid_inventory', '=', 'a.uid_inventory')
            ->join('products as p', 'b.product_id', '=', 'p.id')
            ->join('order_transfers as od', 'a.uid_lead', '=', 'od.uid_lead')
            ->join('payment_terms as pt', 'od.payment_term', '=', 'pt.id')
            ->join('master_bins as bin', 'a.master_bin_id', '=', 'bin.id')

            ->join('users as u', 'a.created_by', '=', 'u.id')
            ->join('users as uc', 'od.contact', '=', 'uc.id')
            ->where('a.inventory_type', 'konsinyasi')
            ->where('a.inventory_status', 'alocated')
            ->where('a.company_id', auth()->user()->company_id)
            ->where('od.status_so', '0');

        $search = $request->search;
        if ($search) {
            // Group the OR conditions within a closure
            $orderItems->where(function ($query) use ($search) {
                $query->orWhere('od.order_number', 'like', "%$search%")
                    ->orWhere('u.name', 'like', "%$search%")
                    ->orWhere('pt.name', 'like', "%$search%");
            });
        }

        if ($request->master_bin_id) {
            $orderItems->where('a.master_bin_id', $request->master_bin_id);
        }

        return response()->json([
            'message' => 'success',
            'status' => 200,
            'data' => $orderItems->groupBy('b.master_bin_id')->orderBy('od.created_at', 'DESC')->paginate($request->perpage),
        ]);
    }

    public function orderKonsinyasiTemplateItem($inventory_id)
    {
        $orderItems = DB::table('products as p')
            ->select(
                'a.id',
                'od.order_number',
                'uc.uid',
                'u.name as created_by_name',
                'pt.name as payment_term_name',
                'a.created_at',
                'p.name as product_name',
                'b.product_id',
                // 'b.qty',
                'b.price_nego',
                DB::raw('tbl_b.price_nego/tbl_b.qty as price_per_unit'),
                'b.discount',
                'b.from_warehouse_id',
                'b.tax_id',
                'mb.name as master_bin_name',
                'b.master_bin_id',
                DB::raw('(SELECT COALESCE(SUM(stock), 0) FROM tbl_master_bin_stocks as mb WHERE mb.product_id = tbl_b.product_id AND mb.master_bin_id = tbl_b.master_bin_id) as qty')
            )
            ->join('inventory_detail_items as b', 'p.id', '=', 'b.product_id')
            ->join('inventory_product_stocks as a', 'b.uid_inventory', '=', 'a.uid_inventory')
            ->join('order_transfers as od', 'a.uid_lead', '=', 'od.uid_lead')
            ->join('payment_terms as pt', 'od.payment_term', '=', 'pt.id')
            ->join('master_bins as mb', 'mb.id', '=', 'b.master_bin_id')
            ->join('users as u', 'a.created_by', '=', 'u.id')
            ->join('users as uc', 'od.contact', '=', 'uc.id')
            ->where('b.master_bin_id', $inventory_id)
            ->groupBy('p.id')
            ->orderBy('od.created_at', 'DESC')
            ->get();

        return response()->json([
            'message' => 'success',
            'status' => 200,
            'data' => $orderItems,
        ]);
    }

    public function orderKonsinyasiDownloadTemplate(Request $request)
    {
        $file_name = 'order-konsinyasi-import-template.xlsx';
        // Excel::store(new ImportKonsinyasiTemplate($request->data), $file_name, 'public');
        // dd($request->data);
        Excel::store(new ImportKonsinyasiTemplate($request->data), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            // 'data' => Storage::url($file_name),
            'message' => 'Template Import'
        ]);
    }

    public function inventoryStock(Request $request)
    {
        $search = $request->search;
        $warehouse_id = $request->warehouse_id;
        $status = $request->status;
        $account_id = $request->account_id;

        $inventory = InventoryProductStock::query()->where('inventory_type', $request->inventory_type);

        if ($search) {
            $inventory->where(function ($query) use ($search, $request) {
                $query->where('status', 'like', "%$search%")
                    ->orWhere('reference_number', 'like', "%$search%")
                    ->orWhere('vendor', 'like', "%$search%");

                // Hanya jika inventory_type adalah 'konsinyasi'
                if ($request->inventory_type == 'konsinyasi') {
                    $query->orWhereHas('orderTransfer', function ($subQuery) use ($search) {
                        $subQuery->where('order_number', 'like', "%$search%");
                        $subQuery->orWhereHas('masterBin', function ($subQuery) use ($search) {
                            $subQuery->where('master_bins.name', 'like', "%$search%");
                        });
                    });
                }

                // Hanya jika inventory_type adalah 'adjustment'
                if ($request->inventory_type == 'adjustment') {
                    $query->orWhereHas('masterBin', function ($subQuery) use ($search) {
                        $subQuery->where('master_bins.name', 'like', "%$search%");
                    });
                    $query->orWhereHas('userCreated', function ($subQuery) use ($search) {
                        $subQuery->where('users.name', 'like', "%$search%");
                    });
                }
            });
        }

        if ($warehouse_id) {
            $inventory->where('warehouse_id', $warehouse_id);
        }

        if ($request->transfer_category) {
            $inventory->where('transfer_category', $request->transfer_category);
        }

        if ($status) {
            $inventory->where('inventory_status', $status);
        }

        if ($account_id) {
            $inventory->where('company_id', $account_id);
        }

        $inventories = $inventory->orderBy('created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $inventories
        ]);
    }


    public function inventoryStockDetail($inventory_id)
    {
        $inventory = InventoryProductStock::with(['items', 'historyAllocations', 'detailItems', 'orderTransfer'])->where('uid_inventory', $inventory_id)->first();
        $inventory['order_transfer'] = OrderTransfer::where('uid_inventory', $inventory_id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryStockCreate(Request $request)
    {

        $warehouse = Warehouse::find($request->warehouse_id);
        $warehouse_name = 'FLIMTY';
        if ($warehouse) {
            $warehouse_name = strtoupper(str_replace(' ', '-', $warehouse->name));
        }

        $companyId = '';
        if ($request->account_id) {
            $companyId = $request->account_id;
        }

        $inventory = InventoryProductStock::create([
            'uid_inventory' => hash('crc32', Carbon::now()->format('U')),
            'warehouse_id' => $request->warehouse_id,
            'reference_number' => $this->generateRefNumber($warehouse_name),
            'created_by' => auth()->user()->id,
            'vendor' => $request->vendor,
            'status' => $request->status ?? 'draft',
            'received_date' => $request->received_date,
            'note' => $request->note,
            'company_id' => $companyId
        ]);

        foreach ($request->items as $item) {
            $inventory->items()->create([
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'subtotal' => $item['sub_total'],
                'type' => 'stock'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryStockUpdate(Request $request, $inventory_id)
    {
        $inventory = InventoryProductStock::where('uid_inventory', $inventory_id)->first();

        $inventory->update([
            'warehouse_id' => $request->warehouse_id,
            'created_by' => auth()->user()->id,
            'vendor' => $request->vendor,
            'status' => $request->status ?? 'draft',
            'received_date' => $request->received_date,
            'note' => $request->note,
            'company_id'  => $request->account_id,
        ]);

        $inventory->items()->delete();
        foreach ($request->items as $item) {
            $inventory->items()->create([
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'subtotal' => $item['sub_total'],
                'type' => 'stock'
            ]);
        }

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Update Stock Inventory - ' . $inventory->uid_inventory,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryStockCancel(Request $request, $inventory_id)
    {
        $inventory = InventoryProductStock::find($inventory_id);
        if ($inventory) {
            $purchaseOrderItem = PurchaseOrderItem::where('ref', $inventory->uid_inventory)->first();
            if ($purchaseOrderItem) {
                if ($purchaseOrderItem->is_allocated == 1) {
                    $product = Product::find($purchaseOrderItem->product_id);
                    $master_stock = (int) getStock($product->stock_warehouse, $inventory->warehouse_id, $inventory->company_id, $product->id);
                    $total_qty_master = $purchaseOrderItem->qty_diterima;
                    $current_stock = $master_stock + $total_qty_master;
                    foreach ($product->variants as $key => $variant_item) {
                        if ($variant_item) {
                            $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
                            $data_stock = [
                                'product_variant_id'  => $variant_item->id,
                                'warehouse_id'  => $inventory->warehouse_id,
                                'qty'  => $current_stock,
                                'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
                                'company_id' => $inventory->company_id,
                            ];
                            ProductVariantStock::updateOrCreate([
                                'product_variant_id'  => $variant_item->id,
                                'warehouse_id'  => $inventory->warehouse_id,
                                'company_id' => $inventory->company_id,
                            ], $data_stock);
                            saveLogStock([
                                'product_id' => $variant_item->product_id,
                                'product_variant_id' => $variant_item->id,
                                'company_id' => $inventory->company_id,
                                'warehouse_id' => $inventory->warehouse_id,
                                'type_product' => 'variant',
                                'type_stock' => 'in',
                                'type_transaction' => null,
                                'type_history' => 'cancel receiving po',
                                'name' => 'cancel receiving po product',
                                'qty' => floor($current_stock / $qty_bundling_variant),
                                'first_stock' => $master_stock,
                                'description' => 'Inventory cancel receiving po 2 - ' . $inventory->uid_inventory,
                            ]);
                        }
                    }
                    $data_stock_1 = [
                        'uid_inventory'  => $inventory->uid_inventory,
                        'warehouse_id'  => $inventory->warehouse_id,
                        'product_id'  => $purchaseOrderItem->product_id,
                        'stock'  => $current_stock,
                        'ref' => $inventory->uid_inventory,
                        'company_id' => $inventory->company_id,
                        'is_allocated' => 1,
                    ];
                    ProductStock::updateOrCreate([
                        'warehouse_id'  => $inventory->warehouse_id,
                        'product_id'  => $purchaseOrderItem->product_id,
                        'company_id' => $inventory->company_id,
                    ], $data_stock_1);
                    saveLogStock([
                        'product_id' => $purchaseOrderItem->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $inventory->warehouse_id,
                        'company_id' => $inventory->company_id,
                        'type_product' => 'master',
                        'type_stock' => 'in',
                        'type_transaction' => null,
                        'type_history' => 'cancel receiving po',
                        'name' => 'cancel receiving po product',
                        'qty' => $total_qty_master,
                        'first_stock' => $master_stock,
                        'description' => 'Inventory cancel receiving po 2 - ' . $inventory->uid_inventory,
                    ]);
                }
                $purchaseOrderItem->update(['qty_diterima' => 0, 'status' => 0, 'is_allocated' => 0]);
            }
            $inventory->update([
                'status' => 'cancel',
                'inventory_status' => 'canceled',
            ]);
            // $dataLog = [
            //     'log_type' => '[fis-dev]inventory',
            //     'log_description' => 'Cancel Stock Inventory - ' . $inventory_id,
            //     'log_user' => auth()->user()->name,
            // ];
            // CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            return response()->json([
                'status' => 'success',
                'data' => $inventory
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'gagal dibatalkan',
            'data' => null
        ], 400);
    }

    public function inventoryStockAllocated(Request $request, $inventory_id)
    {
        try {
            DB::beginTransaction();
            $inventory = InventoryProductStock::where('uid_inventory', $inventory_id)->first();

            foreach ($request->items as $item) {
                $purchase = PurchaseOrderItem::find($item['id']);
                $purchase->update(['is_allocated' => 1]);
                StockAllocationHistory::create([
                    'uid_inventory' => $inventory_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty_alocation'],
                    'from_warehouse_id' => $item['from_warehouse_id'],
                    'to_warehouse_id' => $item['to_warehouse_id'],
                    'sku' => $item['sku'],
                    'u_of_m' => $item['u_of_m'],
                    'transfer_date' => date('Y-m-d'),
                ]);

                // update stock
                $product = Product::find($item['product_id']);
                $master_stock = (int) getStock($product->stock_warehouse, $item['to_warehouse_id'], $inventory->company_id, $product->id);

                // update stock variant
                $variants = ProductVariant::where('product_id', $item['product_id'])->get();
                foreach ($variants as $variant) {
                    // if ($variant->is_bundling > 0) {
                    //     $bundlings = ProductVariantBundling::where('product_variant_id', $variant->id)->get();
                    //     foreach ($bundlings as $key => $bundling) {
                    //         $product = Product::find($bundling->product_id);
                    //         $master_stock_bundling = (int) getStock($product->stock_warehouse, $inventory->warehouse_id, $inventory->company_id, $product->id);
                    //         $qty_bundling = $bundling->product_qty > 0 ? $bundling->product_qty : 1;
                    //         $variants = ProductVariant::where('product_id', $bundling->product_id)->get();
                    //         $total_qty_master = $item['qty_alocation'] * $qty_bundling;
                    //         $current_stock = $master_stock + $total_qty_master;
                    //         foreach ($variants as $variant_item) {
                    //             $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
                    //             $data_stock = [
                    //                 'product_variant_id'  => $variant_item->id,
                    //                 'warehouse_id'  => $inventory->warehouse_id,
                    //                 'qty'  => $current_stock,
                    //                 'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
                    //                 'company_id' => $inventory->company_id,
                    //             ];
                    //             ProductVariantStock::updateOrCreate([
                    //                 'product_variant_id'  => $variant_item->id,
                    //                 'warehouse_id'  => $inventory->warehouse_id,
                    //                 'company_id' => $inventory->company_id,
                    //             ], $data_stock);
                    //             saveLogStock([
                    //                 'product_id' => $variant_item->product_id,
                    //                 'product_variant_id' => $variant_item->id,
                    //                 'company_id' => $inventory->company_id,
                    //                 'warehouse_id' => $inventory->warehouse_id,
                    //                 'type_product' => 'variant',
                    //                 'type_stock' => 'in',
                    //                 'type_transaction' => null,
                    //                 'type_history' => 'purchase',
                    //                 'name' => 'alocated product',
                    //                 'qty' => floor($current_stock / $qty_bundling_variant),
                    //                 'first_stock' => $master_stock,
                    //                 'description' => 'Purcase Order Alocated bundling - ' . $inventory->uid_inventory,
                    //             ]);
                    //         }

                    //         $total_qty = $item['qty_alocation'] * $qty_bundling;
                    //         $data_stock_1 = [
                    //             'uid_inventory'  => $inventory->uid_inventory,
                    //             'warehouse_id'  => $inventory->warehouse_id,
                    //             'product_id'  => $bundling->product_id,
                    //             'stock'  => $master_stock + $total_qty,
                    //             'ref' => $inventory->uid_inventory,
                    //             'company_id' => $inventory->company_id,
                    //             'is_allocated' => 1,
                    //         ];
                    //         ProductStock::updateOrCreate([
                    //             'warehouse_id'  => $inventory->warehouse_id,
                    //             'product_id'  => $bundling->product_id,
                    //             'company_id' => $inventory->company_id,
                    //         ], $data_stock_1);
                    //         saveLogStock([
                    //             'product_id' => $bundling->product_id,
                    //             'product_variant_id' => null,
                    //             'warehouse_id' => $inventory->warehouse_id,
                    //             'company_id' => $inventory->company_id,
                    //             'type_product' => 'master',
                    //             'type_stock' => 'in',
                    //             'type_transaction' => null,
                    //             'type_history' => 'purchase',
                    //             'name' => 'alocated product',
                    //             'qty' => $total_qty,
                    //             'first_stock' => $master_stock,
                    //             'description' => 'Purcase Order Alocated bundling - ' . $inventory->uid_inventory,
                    //         ]);
                    //     }
                    // } else {
                    $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                    $total_qty = $master_stock + $item['qty_alocation'];

                    ProductVariantStock::updateOrCreate(['product_variant_id' => $variant->id, 'warehouse_id' => $item['to_warehouse_id'], 'company_id' => $inventory->company_id,], [
                        'product_variant_id' => $variant->id,
                        'qty' => $total_qty,
                        'stock_of_market' => floor($total_qty / $qty_bundling) ?? 0,
                        'warehouse_id' => $item['to_warehouse_id'],
                        'company_id' => $inventory->company_id,
                    ]);

                    saveLogStock([
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $item['to_warehouse_id'],
                        'type_product' => 'variant',
                        'type_stock' => 'in',
                        'company_id' => $inventory->company_id,
                        'type_transaction' => null,
                        'type_history' => 'purchase',
                        'name' => 'alocated product',
                        'qty' => $item['qty_alocation'],
                        'first_stock' => $master_stock,
                        'description' => 'Purcase Order Alocated - ' . $inventory->reference_number,
                    ]);
                    // }
                }



                ProductStock::updateOrCreate([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['from_warehouse_id'],
                    'company_id' => $inventory->company_id,
                ], [
                    'uid_inventory' => $inventory_id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['to_warehouse_id'],
                    'stock' => $master_stock + $item['qty_alocation'],
                    'is_allocated' => 1,
                    'company_id' => $inventory->company_id,
                ]);




                // log products
                saveLogStock([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => null,
                    'warehouse_id' => $item['to_warehouse_id'],
                    'type_product' => 'master',
                    'type_stock' => 'in',
                    'type_transaction' => null,
                    'type_history' => 'purchase',
                    'name' => 'alocated product',
                    'qty' => $item['qty_alocation'],
                    'first_stock' => $master_stock,
                    'company_id' => $inventory->company_id,
                    'description' => 'Purcase Order Alocated - ' . $inventory->reference_number,
                ]);
            }

            $inventory->update([
                'inventory_status' => 'alocated',
                'allocated_by' => auth()->user()->id,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses Allokasi Gagal',
                'error' =>  $th->getMessage()
            ], 400);
        }
    }


    public function inventoryTransferCreate(Request $request)
    {
        try {
            DB::beginTransaction();
            $companyId = $request->account_id;
            $so_ethix = $request->so_ethix ?? $this->generateSONumber();

            $user = auth()->user();
            $role_type = $user->role->role_type;
            $sales = $user->id;
            if ($role_type == 'sales' && $request->sales) {
                $sales = $request->sales['value'];
            }

            $uid_inventory = $request->inventory_id ?? generateUid();
            // $inventory =  InventoryProductStock::where('reference_number', $request->po_number)->first();
            $dataTransfer = [
                'uid_inventory' => $uid_inventory,
                'uid_lead' => $uid_inventory,
                'allocated_by' => auth()->user()->id,
                'warehouse_id' => $request->warehouse_id,
                'destination_warehouse_id' => $request->to_warehouse_id,
                'product_id' => @$request->product_id,
                'reference_number' => @$request->po_number,
                'created_by' => $request->created_by ?? $user->id,
                'vendor' => $request->vendor,
                'inventory_status' => 'draft',
                'inventory_type' => $request->inventory_type,
                'status' => 'draft',
                'received_date' => date('Y-m-d'),
                'note' => $request->note,
                'company_id' => $companyId,
                'so_ethix' => $so_ethix,
                'post_ethix' => $request->post_ethix,
                'transfer_category' => $request->transfer_category ?? 'new',
                'is_konsinyasi' => ($request->inventory_type == 'konsinyasi') ? '1' : '0'
            ];

            $bin_id = isset($request->master_bin_id['value']) ? $request->master_bin_id['value'] : $request->master_bin_id;
            if ($request->inventory_type == 'konsinyasi') {
                $dataTransfer['master_bin_id'] = $bin_id;
            }
            $inventory = InventoryProductStock::updateOrCreate(['uid_inventory' => $uid_inventory], $dataTransfer);

            if ($request->inventory_type == 'konsinyasi') {
                $so_number = $request->order_number ?? OrderTransfer::generateOrderNumber(6);
                $si_number =  $request->invoice_number ?? OrderTransfer::generateInvoiceNumber(6);
                $order = OrderTransfer::updateOrCreate(['uid_lead' => $inventory->uid_lead], [
                    'uid_lead' => $inventory->uid_lead,
                    'uid_inventory' => $inventory->uid_inventory,
                    'title' => $so_number,
                    'order_number' => $so_number,
                    'invoice_number' => $si_number,
                    'transfer_number' => $so_ethix,
                    'contact'  => $request->contact['value'],
                    'sales'  => $sales,
                    'warehouse_id' => $inventory->warehouse_id,
                    'master_bin_id' => $inventory->master_bin_id,
                    'payment_term'  => $request->payment_term,
                    'preference_number' => $request->preference_number,
                    'notes' => $request->notes,
                    'status'  => 'draft',
                    'company_id' => $companyId,
                ]);

                // try {
                //     DB::beginTransaction();
                //     InventoryDetailItem::where('uid_inventory', $inventory->uid_inventory)->whereNotIn('id', $request->itemkons->pluck('id'))->delete();
                //     DB::commit();
                // } catch (\Throwable $th) {
                //     DB::rollBack();
                //     setSetting('error_deleting', 'item');
                // }
                $tax_id =  $companyId == 1 ? 1 : null;
                foreach ($request->itemkons as $key => $item) {
                    $product = Product::find($item['product_id']);
                    InventoryDetailItem::updateOrCreate(['id' => $item['id']], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'qty_alocation' => $item['qty'],
                        'from_warehouse_id' => $inventory->warehouse_id,
                        'to_warehouse_id' => $request->to_warehouse_id,
                        'master_bin_id' => $inventory->master_bin_id,
                        'sku' => $product->sku,
                        'u_of_m' => $product->u_of_m,
                        'tax_id' => $item['tax_id'] ?? $tax_id,
                        'tax_amount' => $item['tax_amount'],
                        'tax_percentage' => $item['tax_percentage'],
                        'discount_percentage' => $item['discount_percentage'],
                        'discount' => $item['discount'],
                        'discount_amount' => $item['discount_amount'],
                        'subtotal' => $item['subtotal'],
                        'price_nego' => $item['price_nego'],
                        'total' => $item['total'],
                        'stock_awal' => @$item['stock'] ?? @$item['stock_awal'],
                    ]);
                }
                UpdatePriceQueue::dispatch($order, 'order_transfers')->onQueue('queue-prod');
            } else {
                foreach ($request->items as $key => $item) {
                    $product = Product::find($item['product_id']);

                    InventoryDetailItem::updateOrCreate(['id' => $item['id']], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'qty_alocation' => $item['qty_alocation'],
                        'from_warehouse_id' => $item['from_warehouse_id'],
                        'to_warehouse_id' => $item['to_warehouse_id'],
                        'sku' => $product->sku,
                        'u_of_m' => $product->u_of_m,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan',
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Gagal Disimpan',
                'data' => $th->getMessage(),
                'error' => $th->getTraceAsString()
            ], 400);
        }
    }

    // complete
    public function inventoryTransferComplete($uid_inventory)
    {
        try {
            DB::beginTransaction();
            $inventory =  InventoryProductStock::where('uid_inventory', $uid_inventory)->first();
            $companyId = $inventory->company_id;
            $inventory->update(['status' => 'done', 'inventory_status' => 'alocated']);

            // update stock
            $productItems = [];
            foreach ($inventory->detailItems as $item) {
                $product = Product::find($item->product_id);
                if ($inventory->inventory_type == 'konsinyasi') {
                    $orderKonsiyasi = OrderTransfer::where('uid_lead', $inventory->uid_lead)->first(['id', 'master_bin_id']);

                    //pengurangan stock
                    $master_stock = (int) getStock($product->stock_warehouse, $item->from_warehouse_id, $companyId, $item->product_id);
                    ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                    ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId]);

                    $qty_alocation = $item['qty'];

                    ProductStock::updateOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->from_warehouse_id,
                        'stock' => $master_stock - $qty_alocation,
                        'is_allocated' => 1,
                        'company_id' => $inventory->company_id,
                    ]);

                    // out
                    saveLogStock([
                        'product_id' => $item->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $item->from_warehouse_id,
                        'type_product' => 'master',
                        'type_stock' => 'out',
                        'type_transaction' => null,
                        'type_history' => 'transfer',
                        'name' => 'transfer product',
                        'qty' => $item['qty'],
                        'company_id' => $inventory->company_id,
                        'first_stock' => $master_stock,
                        'description' => 'Konsiyasi transfer - ' . $inventory->uid_inventory,
                    ]);

                    // update stock variant
                    $variants = ProductVariant::where('product_id', $item->product_id)->get();
                    foreach ($variants as $variant) {
                        ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();

                        $current_stock = $master_stock - $item['qty']; // 50 - 10 = 40
                        $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

                        $stockmarket =  floor($current_stock / $qty_bundling) ?? 0;
                        ProductVariantStock::updateOrCreate([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->from_warehouse_id,
                            'company_id' => $inventory->company_id,
                        ], [
                            'product_variant_id' => $variant->id,
                            'qty' => $current_stock,
                            'warehouse_id' => $item->from_warehouse_id,
                            'stock_of_market' => $stockmarket < 0 ? 0 : $stockmarket,
                            'company_id' => $inventory->company_id,
                        ]);


                        $subQty = $item['qty'];
                        $binStock = getStockBin($orderKonsiyasi->master_bin_id, $inventory->company_id, $item->product_id);
                        $stockActual = $binStock + $subQty;
                        MasterBinStock::where('master_bin_id', $orderKonsiyasi->master_bin_id)->where('product_id', $item->product_id)->where('company_id', $inventory->company_id)->where('product_variant_id', $variant->id)->where('stock_type', 'new')->delete();
                        MasterBinStock::updateOrCreate([
                            'master_bin_id' => $orderKonsiyasi->master_bin_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'company_id' => $inventory->company_id,
                        ], [
                            'master_bin_id' => $orderKonsiyasi->master_bin_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'company_id' => $inventory->company_id,
                            'stock' => floor($stockActual / $qty_bundling) ?? 0,
                            'stock_type' => 'new',
                            'description' => "Transfer Konsinyasi Barang"
                        ]);

                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->from_warehouse_id,
                            'type_product' => 'variant',
                            'type_stock' => 'out',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => $item['qty'],
                            'company_id' => $inventory->company_id,
                            'first_stock' => $master_stock,
                            'description' => 'Konsiyasi transfer - ' . $inventory->uid_inventory,
                        ]);
                    }
                } else {
                    $productItems[] = [
                        "product_code" => $product->sku,
                        "product_name" => $product->name,
                        "quantity" => $item->qty_alocation,
                        "unit_price" => $product->price['final_price'],
                        "weight" => 1
                    ];

                    $master_stock = (int) getStock($product->stock_warehouse, $item->from_warehouse_id, $companyId, $item->product_id);

                    $qty_alocation = $item->qty_alocation;
                    $final_stock = $master_stock - $qty_alocation;
                    if ($final_stock < 0) {
                        if ($final_stock < 0) {
                            DB::rollBack();
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Alokasi stock gagal, stock tidak mencukupi',
                                'data' => []
                            ], 400);
                        }
                    }

                    $master_stock_to = (int) getStock($product->stock_warehouse, $item->to_warehouse_id, $companyId, $item->product_id);
                    ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                    ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->to_warehouse_id)->where('company_id', $companyId)->delete();
                    ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId]);
                    ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->to_warehouse_id, 'company_id' => $companyId]);


                    ProductStock::updateOrCreate(['product_id' => $item->product_id, 'company_id' => $companyId, 'warehouse_id' => $item->to_warehouse_id], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->to_warehouse_id,
                        'stock' => $master_stock_to + $qty_alocation,
                        'is_allocated' => 1,
                        'company_id' => $inventory->company_id,
                    ]);

                    ProductStock::updateOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->from_warehouse_id,
                        'stock' => $master_stock - $qty_alocation,
                        'is_allocated' => 1,
                        'company_id' => $inventory->company_id,
                    ]);

                    // out
                    saveLogStock([
                        'product_id' => $item->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $item->to_warehouse_id,
                        'type_product' => 'master',
                        'type_stock' => 'in',
                        'type_transaction' => null,
                        'type_history' => 'transfer',
                        'name' => 'transfer product',
                        'qty' => $item->qty_alocation,
                        'company_id' => $inventory->company_id,
                        'first_stock' => $master_stock_to,
                        'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                    ]);

                    saveLogStock([
                        'product_id' => $item->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $item->from_warehouse_id,
                        'type_product' => 'master',
                        'type_stock' => 'out',
                        'type_transaction' => null,
                        'type_history' => 'transfer',
                        'name' => 'transfer product',
                        'qty' => $item->qty_alocation,
                        'company_id' => $inventory->company_id,
                        'first_stock' => $master_stock,
                        'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                    ]);

                    // update stock variant
                    $variants = ProductVariant::where('product_id', $item->product_id)->get();
                    foreach ($variants as $variant) {
                        ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                        ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->to_warehouse_id)->where('company_id', $companyId)->delete();


                        // dd($product->stock_warehouse, $master_stock, $master_stock_to, $item->qty_alocation);
                        $current_stock = $master_stock - $item->qty_alocation; // 50 - 10 = 40
                        $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

                        $stockmarket =  floor($current_stock / $qty_bundling) ?? 0;
                        $stockmarket_to =  $master_stock_to + $item->qty_alocation;
                        ProductVariantStock::updateOrCreate([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->from_warehouse_id,
                            'company_id' => $inventory->company_id,
                        ], [
                            'product_variant_id' => $variant->id,
                            'qty' => $current_stock,
                            'warehouse_id' => $item->from_warehouse_id,
                            'stock_of_market' => $stockmarket < 0 ? 0 : $stockmarket,
                            'company_id' => $inventory->company_id,
                        ]);

                        ProductVariantStock::updateOrCreate([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->to_warehouse_id,
                            'company_id' => $inventory->company_id,
                        ], [
                            'product_variant_id' => $variant->id,
                            'qty' => $stockmarket_to,
                            'warehouse_id' => $item->to_warehouse_id,
                            'stock_of_market' => floor($stockmarket_to / $qty_bundling) ?? 0,
                            'company_id' => $inventory->company_id,
                        ]);

                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->to_warehouse_id,
                            'company_id' => $inventory->company_id,
                            'type_product' => 'variant',
                            'type_stock' => 'in',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => floor($stockmarket_to / $qty_bundling) ?? 0,
                            'first_stock' => $master_stock_to,
                            'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                        ]);

                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->from_warehouse_id,
                            'company_id' => $inventory->company_id,
                            'type_product' => 'variant',
                            'type_stock' => 'out',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => $stockmarket < 0 ? 0 : $stockmarket,
                            'first_stock' => $master_stock,
                            'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                        ]);
                    }

                    $purchase = PurchaseOrderItem::where([
                        'ref' => $inventory->uid_inventory,
                        'product_id' => $item->product_id,
                    ])->first();
                    if ($purchase) {
                        $purchase->update(['is_allocated' => 1]);
                    }
                    StockAllocationHistory::create([
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item->product_id,
                        'quantity' => $item->qty_alocation,
                        'from_warehouse_id' => $item->from_warehouse_id,
                        'to_warehouse_id' => $item->to_warehouse_id,
                        'sku' => $product->sku,
                        'u_of_m' => $product->u_of_m,
                        'transfer_date' => date('Y-m-d'),
                    ]);
                }
            }

            if ($inventory->post_ethix > 0) {
                $company = CompanyAccount::find($inventory->company_id, ['account_code']);
                $log = OrderSubmitLog::create([
                    'submited_by' => auth()->user()->id,
                    'type_si' => 'submit-transfer-ethix',
                    'vat' => 0,
                    'tax' => 0,
                    'ref_id' => $inventory->id,
                    'company_id' => $company->account_code
                ]);
                try {
                    $headers = [
                        'secretcode: ' . getSetting('ETHIX_SECRETCODE_' . $company->account_code),
                        'secretkey: ' . getSetting('ETHIX_SECRETKEY_' . $company->account_code),
                        'Content-Type: application/json'
                    ];


                    $curl_post_data = array(
                        "client_code" => getSetting('ETHIX_CLIENTCODE_' . $company->account_code),
                        "location_code" => $inventory->warehouse->ethix_id,
                        "courier_name" => "ANTER AJA",
                        "delivery_type" => "REGULER",
                        "order_type" => "TFW",
                        "order_date" => "2023-08-23",
                        "order_code" => $inventory->so_ethix,
                        "channel_origin_name" => "FIS",
                        "payment_date" => "2023-08-23 19:29:00",
                        "is_cashless" => true,
                        "recipient_name" => "Fikar",
                        "recipient_phone" => "08888888",
                        "recipient_subdistrict" => "Ciputat",
                        "recipient_district" => "Cipayung",
                        "recipient_city" => "Tangsel",
                        "recipient_province" => "Banten",
                        "recipient_country" => "Indonesia",
                        "recipient_address" => "jalan darat",
                        "recipient_postal_code" => "12270",
                        "Agent" => "Vidi",
                        "product_price" => "20000",
                        "product_discount" => "",
                        "shipping_price" => "",
                        "shipping_discount" => "",
                        "insurance_price" => "",
                        "total_price" => "14750000",
                        "total_weight" => "1",
                        "total_koli" => "1",
                        "cod_price" => "0",
                        "product_discount" => "0",
                        "shipping_price" => "9000",
                        "shipping_discount" => "0",
                        "insurance_price" => "0",
                        "created_via" => "FIS System",
                        "product_information" => $productItems,
                    );

                    $url = 'https://wms.ethix.id/index.php?r=Externalv2/Order/PostOrder';
                    $handle = curl_init();
                    curl_setopt($handle, CURLOPT_URL, $url);
                    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle, CURLOPT_TIMEOUT, 9000);
                    curl_setopt($handle, CURLOPT_POST, true);
                    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
                    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
                    $responseData = curl_exec($handle);
                    curl_close($handle);

                    setSetting('ethix_so_manual_transfer', json_encode($responseData));
                    $responseJSON = json_decode($responseData, true);
                    if (!$responseJSON && is_string($responseData)) {
                        // $inventory->update(['status_ethix_submit' => 'needsubmited']);
                        $log->update(['body' => json_encode($curl_post_data)]);
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $log->id,
                            'order_id' => $inventory->id
                        ], [
                            'order_submit_log_id' => $log->id,
                            'order_id' => $inventory->id,
                            'status' => 'failed',
                            'error_message' => $responseData
                        ]);
                        return;
                    }

                    // Check if any error occured
                    if (curl_errno($handle)) {
                        $log->update(['body' => json_encode($curl_post_data)]);
                        // $inventory->update(['status_ethix_submit' => 'needsubmited']);
                        return;
                    }


                    if (isset($responseJSON['status'])) {
                        if (in_array($responseJSON['status'], [200, 201])) {
                            // $inventory->update(['status_ethix_submit' => 'submited']);
                            $log->update(['body' => null]);
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $log->id,
                                'order_id' => $inventory->id
                            ], [
                                'order_submit_log_id' => $log->id,
                                'order_id' => $inventory->id,
                                'status' => 'success',
                                'error_message' => 'SUCCESS SUBMIT ETHIX'
                            ]);
                        } else {
                            // $inventory->update(['status_ethix_submit' => 'needsubmited']);
                            $log->update(['body' => json_encode($curl_post_data)]);
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $log->id,
                                'order_id' => $inventory->id
                            ], [
                                'order_submit_log_id' => $log->id,
                                'order_id' => $inventory->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['message']
                            ]);
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    setSetting('ethix_so_manual_error_transfer', $th->getMessage());
                }
            }

            DB::commit();
            $message = $inventory->inventory_type == 'konsinyasi' ? 'Approval data item transfer konsinyasi berhasil dilakukan!' : 'Approval data item transfer berhasil dilakukan!';
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses alokasi gagal',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    // process for approve adjustment
    public function inventoryAdjustmentProcess($uid_inventory)
    {
        try {
            DB::beginTransaction();
            $inventory =  InventoryProductStock::where('uid_inventory', $uid_inventory)->first();
            $inventory->update(['status' => 'waiting']);
            $companyId = $inventory->company_id;

            DB::commit();
            $message = 'Berhasil melakukan proses approval';
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses alokasi gagal',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    // process for reject adjustment
    public function inventoryAdjustmentReject($uid_inventory)
    {
        try {
            DB::beginTransaction();
            $inventory =  InventoryProductStock::where('uid_inventory', $uid_inventory)->first();
            $inventory->update(['status' => 'reject']);
            $companyId = $inventory->company_id;

            DB::commit();
            $message = 'Berhasil melakukan reject';
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses alokasi gagal',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    // approve adjustment
    public function inventoryAdjustmentApprove($uid_inventory)
    {
        try {
            DB::beginTransaction();

            // Fetch inventory and update its status
            $inventory = InventoryProductStock::where('uid_inventory', $uid_inventory)->first();
            $inventory->update(['status' => 'done', 'inventory_status' => 'allocated']);

            $companyId = $inventory->company_id;

            foreach ($inventory->detailItems as $item) {
                $productId = $item->product_id;
                $warehouseId = $inventory->warehouse_id;
                $quantity = $item->qty;

                if ($item->from_warehouse_id == 19) {
                    // Update stock for master bin and variants
                    $this->updateMasterBinStock($inventory, $item, $companyId);
                } else {
                    // Update master product stock
                    $this->updateMasterStock($productId, $warehouseId, $companyId, $quantity, $inventory->uid_inventory);

                    // Update variant stocks if applicable
                    $this->updateVariantStock($productId, $warehouseId, $companyId, $quantity, $inventory->uid_inventory);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock Adjustment Berhasil Disetujui',
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Stock adjustment failed',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    private function updateMasterStock($productId, $warehouseId, $companyId, $quantity, $inventoryUid)
    {
        $currentStock = (int) getStock(null, $warehouseId, $companyId, $productId);

        $adjustedStock = $currentStock + $quantity;

        ProductStock::updateOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId, 'company_id' => $companyId],
            ['stock' => $adjustedStock]
        );

        saveLogStock([
            'product_id' => $productId,
            'product_variant_id' => null,
            'warehouse_id' => $warehouseId,
            'company_id' => $companyId,
            'type_product' => 'master',
            'type_stock' => 'adjust',
            'type_transaction' => null,
            'type_history' => 'adjustment',
            'name' => 'stock adjustment',
            'qty' => $quantity,
            'first_stock' => $currentStock,
            'description' => 'Stock adjustment - ' . $inventoryUid,
        ]);
    }

    private function updateVariantStock($productId, $warehouseId, $companyId, $quantity, $inventoryUid)
    {
        $currentStock = (int) getStock(null, $warehouseId, $companyId, $productId);
        $adjustedStock = $currentStock + $quantity;

        $variants = ProductVariant::where('product_id', $productId)->get();

        foreach ($variants as $variant) {
            $variantId = $variant->id;
            $variantCurrentStock = ProductVariantStock::where('product_variant_id', $variantId)
                ->where('warehouse_id', $warehouseId)
                ->where('company_id', $companyId)
                ->value('qty') ?? 0;

            $variantAdjustedStock = $adjustedStock;
            $qtyBundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

            ProductVariantStock::updateOrCreate(
                ['product_variant_id' => $variantId, 'warehouse_id' => $warehouseId, 'company_id' => $companyId],
                ['qty' => $variantAdjustedStock, 'stock_of_market' => floor($variantAdjustedStock / $qtyBundling)]
            );

            saveLogStock([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'company_id' => $companyId,
                'type_product' => 'variant',
                'type_stock' => 'adjust',
                'type_transaction' => null,
                'type_history' => 'adjustment',
                'name' => 'variant stock adjustment',
                'qty' => $quantity,
                'first_stock' => $variantCurrentStock,
                'description' => 'Variant stock adjustment - ' . $inventoryUid,
            ]);
        }
    }

    private function updateMasterBinStock($inventory, $item, $companyId)
    {
        $product = Product::find($item->product_id);
        $master_stock = (int)getStock($product->stock_warehouse, $item->from_warehouse_id, $companyId, $item->product_id);
        $qty_allocation = $item['qty'];

        // Update stock variant for source warehouse
        $variants = ProductVariant::where('product_id', $item->product_id)->get();
        foreach ($variants as $variant) {
            // Create entry in MasterBinStock
            $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

            $subQty = $item['qty'];
            $binStock = getStockBin($inventory->master_bin_id, $inventory->company_id, $item->product_id);
            $stockActual = $binStock + $subQty;
            MasterBinStock::where('master_bin_id', $inventory->master_bin_id)->where('product_id', $item->product_id)->where('company_id', $inventory->company_id)->where('product_variant_id', $variant->id)->where('stock_type', 'new')->delete();
            MasterBinStock::updateOrCreate([
                'master_bin_id' => $inventory->master_bin_id,
                'product_id' => $item->product_id,
                'product_variant_id' => $variant->id,
                'company_id' => $inventory->company_id,
            ], [
                'master_bin_id' => $inventory->master_bin_id,
                'product_id' => $item->product_id,
                'product_variant_id' => $variant->id,
                'company_id' => $inventory->company_id,
                'stock' => floor($stockActual / $qty_bundling) ?? 0,
                'stock_type' => 'new',
                'description' => "Stok Adjustment Barang"
            ]);

            // Log stock adjust
            saveLogStock([
                'product_id' => $item->product_id,
                'product_variant_id' => $variant->id,
                'warehouse_id' => $item->from_warehouse_id,
                'company_id' => $inventory->company_id,
                'type_product' => 'variant',
                'type_stock' => 'in',
                'type_transaction' => null,
                'type_history' => 'adjustment',
                'name' => 'adjustment product',
                'qty' => $item['qty'],
                'first_stock' => $master_stock,
                'description' => 'Adjustment - ' . $inventory->uid_inventory,
            ]);
        }
    }


    // approve
    public function inventoryTransferApprove($uid_inventory)
    {
        try {
            DB::beginTransaction();
            $inventory =  InventoryProductStock::where('uid_inventory', $uid_inventory)->first();
            // $companyId = $inventory->company_id;
            $inventory->update(['status' => 'done', 'inventory_status' => 'alocated']);
            $companyId = $inventory->company_id;
            foreach ($inventory->detailItems as $item) {
                $product = Product::find($item->product_id);
                if ($inventory->inventory_type == 'konsinyasi') {
                    $orderKonsiyasi = OrderTransfer::where('uid_lead', $inventory->uid_lead)->first(['id', 'master_bin_id']);

                    //pengurangan stock
                    $master_stock = (int) getStock($product->stock_warehouse, $item->from_warehouse_id, $companyId, $item->product_id);

                    $qty_alocation = $item['qty'];
                    $final_stock = $master_stock - $qty_alocation;
                    if ($final_stock < 0) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Alokasi stock gagal, stock tidak mencukupi',
                            'data' => []
                        ], 400);
                    }

                    ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                    ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId]);

                    $qty_alocation = $item['qty'];

                    ProductStock::updateOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->from_warehouse_id,
                        'stock' => $master_stock - $qty_alocation,
                        'is_allocated' => 1,
                        'company_id' => $inventory->company_id,
                    ]);

                    // out
                    saveLogStock([
                        'product_id' => $item->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $item->from_warehouse_id,
                        'company_id' => $inventory->company_id,
                        'type_product' => 'master',
                        'type_stock' => 'out',
                        'type_transaction' => null,
                        'type_history' => 'transfer',
                        'name' => 'transfer product',
                        'qty' => $item['qty'],
                        'first_stock' => $master_stock,
                        'description' => 'Konsiyasi transfer - ' . $inventory->uid_inventory,
                    ]);

                    // update stock variant
                    $variants = ProductVariant::where('product_id', $item->product_id)->get();
                    foreach ($variants as $variant) {
                        ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();

                        $current_stock = $master_stock - $item['qty']; // 50 - 10 = 40
                        $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

                        $stockmarket =  floor($current_stock / $qty_bundling) ?? 0;
                        ProductVariantStock::updateOrCreate([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->from_warehouse_id,
                            'company_id' => $inventory->company_id,
                        ], [
                            'product_variant_id' => $variant->id,
                            'qty' => $current_stock,
                            'warehouse_id' => $item->from_warehouse_id,
                            'stock_of_market' => $stockmarket < 0 ? 0 : $stockmarket,
                            'company_id' => $inventory->company_id,
                        ]);

                        $subQty = $item['qty'];
                        $binStock = getStockBin($orderKonsiyasi->master_bin_id, $inventory->company_id, $item->product_id);
                        $stockActual = $binStock + $subQty;
                        MasterBinStock::where('master_bin_id', $orderKonsiyasi->master_bin_id)->where('product_id', $item->product_id)->where('company_id', $inventory->company_id)->where('product_variant_id', $variant->id)->where('stock_type', 'new')->delete();
                        MasterBinStock::updateOrCreate([
                            'master_bin_id' => $orderKonsiyasi->master_bin_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'company_id' => $inventory->company_id,
                        ], [
                            'master_bin_id' => $orderKonsiyasi->master_bin_id,
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'company_id' => $inventory->company_id,
                            'stock' => floor($stockActual / $qty_bundling) ?? 0,
                            'stock_type' => 'new',
                            'description' => "Transfer Konsinyasi Barang"
                        ]);

                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $item->from_warehouse_id,
                            'company_id' => $inventory->company_id,
                            'type_product' => 'variant',
                            'type_stock' => 'out',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => $item['qty'],
                            'first_stock' => $master_stock,
                            'description' => 'Konsiyasi transfer - ' . $inventory->uid_inventory,
                        ]);
                    }

                    // send to virtual warehouse
                    if ($inventory->transfer_category == 'new') {
                        $productItems[] = [
                            "product_code" => $product->sku,
                            "product_name" => $product->name,
                            "quantity" => $item->qty_alocation,
                            "unit_price" => $product->price['final_price'],
                            "weight" => 1
                        ];

                        $master_stock = (int) getStock($product->stock_warehouse, $item->from_warehouse_id, $companyId, $item->product_id);

                        $qty_alocation = $item->qty_alocation;
                        $final_stock = $master_stock - $qty_alocation;
                        if ($final_stock < 0) {
                            if ($final_stock < 0) {
                                DB::rollBack();
                                return response()->json([
                                    'status' => 'error',
                                    'message' => 'Alokasi stock gagal, stock tidak mencukupi',
                                    'data' => []
                                ], 400);
                            }
                        }

                        $master_stock_to = (int) getStock($product->stock_warehouse, $item->to_warehouse_id, $companyId, $item->product_id);
                        // ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                        ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->to_warehouse_id)->where('company_id', $companyId)->delete();
                        // ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId]);
                        ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->to_warehouse_id, 'company_id' => $companyId]);


                        ProductStock::updateOrCreate(['product_id' => $item->product_id, 'company_id' => $companyId, 'warehouse_id' => $item->to_warehouse_id], [
                            'uid_inventory' => $inventory->uid_inventory,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $item->to_warehouse_id,
                            'stock' => $master_stock_to + $qty_alocation,
                            'is_allocated' => 1,
                            'company_id' => $inventory->company_id,
                        ]);

                        // ProductStock::updateOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId], [
                        //     'uid_inventory' => $inventory->uid_inventory,
                        //     'product_id' => $item->product_id,
                        //     'warehouse_id' => $item->from_warehouse_id,
                        //     'stock' => $master_stock - $qty_alocation,
                        //     'is_allocated' => 1,
                        //     'company_id' => $inventory->company_id,
                        // ]);

                        // out
                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => null,
                            'warehouse_id' => $item->to_warehouse_id,
                            'type_product' => 'master',
                            'type_stock' => 'in',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => $item->qty_alocation,
                            'company_id' => $inventory->company_id,
                            'first_stock' => $master_stock_to,
                            'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                        ]);

                        // saveLogStock([
                        //     'product_id' => $item->product_id,
                        //     'product_variant_id' => null,
                        //     'warehouse_id' => $item->from_warehouse_id,
                        //     'type_product' => 'master',
                        //     'type_stock' => 'out',
                        //     'type_transaction' => null,
                        //     'type_history' => 'transfer',
                        //     'name' => 'transfer product',
                        //     'qty' => $item->qty_alocation,
                        //     'company_id' => $inventory->company_id,
                        //     'first_stock' => $master_stock,
                        //     'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                        // ]);

                        // update stock variant
                        $variants = ProductVariant::where('product_id', $item->product_id)->get();
                        foreach ($variants as $variant) {
                            // ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                            ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->to_warehouse_id)->where('company_id', $companyId)->delete();


                            // dd($product->stock_warehouse, $master_stock, $master_stock_to, $item->qty_alocation);
                            $current_stock = $master_stock - $item->qty_alocation; // 50 - 10 = 40
                            $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

                            $stockmarket =  floor($current_stock / $qty_bundling) ?? 0;
                            $stockmarket_to =  $master_stock_to + $item->qty_alocation;
                            // ProductVariantStock::updateOrCreate([
                            //     'product_variant_id' => $variant->id,
                            //     'warehouse_id' => $item->from_warehouse_id,
                            //     'company_id' => $inventory->company_id,
                            // ], [
                            //     'product_variant_id' => $variant->id,
                            //     'qty' => $current_stock,
                            //     'warehouse_id' => $item->from_warehouse_id,
                            //     'stock_of_market' => $stockmarket < 0 ? 0 : $stockmarket,
                            //     'company_id' => $inventory->company_id,
                            // ]);

                            ProductVariantStock::updateOrCreate([
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $item->to_warehouse_id,
                                'company_id' => $inventory->company_id,
                            ], [
                                'product_variant_id' => $variant->id,
                                'qty' => $stockmarket_to,
                                'warehouse_id' => $item->to_warehouse_id,
                                'stock_of_market' => floor($stockmarket_to / $qty_bundling) ?? 0,
                                'company_id' => $inventory->company_id,
                            ]);

                            saveLogStock([
                                'product_id' => $item->product_id,
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $item->to_warehouse_id,
                                'company_id' => $inventory->company_id,
                                'type_product' => 'variant',
                                'type_stock' => 'in',
                                'type_transaction' => null,
                                'type_history' => 'transfer',
                                'name' => 'transfer product',
                                'qty' => floor($stockmarket_to / $qty_bundling) ?? 0,
                                'first_stock' => $master_stock_to,
                                'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                            ]);

                            // saveLogStock([
                            //     'product_id' => $item->product_id,
                            //     'product_variant_id' => $variant->id,
                            //     'warehouse_id' => $item->from_warehouse_id,
                            //     'company_id' => $inventory->company_id,
                            //     'type_product' => 'variant',
                            //     'type_stock' => 'out',
                            //     'type_transaction' => null,
                            //     'type_history' => 'transfer',
                            //     'name' => 'transfer product',
                            //     'qty' => $stockmarket < 0 ? 0 : $stockmarket,
                            //     'first_stock' => $master_stock,
                            //     'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                            // ]);
                        }

                        $purchase = PurchaseOrderItem::where([
                            'ref' => $inventory->uid_inventory,
                            'product_id' => $item->product_id,
                        ])->first();
                        if ($purchase) {
                            $purchase->update(['is_allocated' => 1]);
                        }
                        StockAllocationHistory::create([
                            'uid_inventory' => $inventory->uid_inventory,
                            'product_id' => $item->product_id,
                            'quantity' => $item->qty_alocation,
                            'from_warehouse_id' => $item->from_warehouse_id,
                            'to_warehouse_id' => $item->to_warehouse_id,
                            'sku' => $product->sku,
                            'u_of_m' => $product->u_of_m,
                            'transfer_date' => date('Y-m-d'),
                        ]);
                    }
                }
            }

            DB::commit();
            $message = $inventory->inventory_type == 'konsinyasi' ? 'Approval data item transfer konsinyasi berhasil dilakukan!' : 'Approval data item transfer berhasil dilakukan!';
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses alokasi gagal',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    // cancel
    public function inventoryTransferCancel($uid_inventory)
    {
        try {
            DB::beginTransaction();
            $inventory =  InventoryProductStock::where('uid_inventory', $uid_inventory)->first();
            $companyId = $inventory->company_id;
            $inventory->update(['status' => 'cancel', 'inventory_status' => 'canceled']);
            // update stock
            if ($inventory->status == 'done') {
                foreach ($inventory->detailItems as $item) {
                    $product = Product::find($item->product_id);
                    if ($inventory->inventory_type == 'transfer') {
                        $to_warehouse = $item->from_warehouse_id;
                        $from_warehouse = $item->to_warehouse_id;
                        $master_stock = (int) getStock($product->stock_warehouse, $to_warehouse, $companyId, $item->product_id);

                        $qty_alocation = $item->qty_alocation;
                        $final_stock = $master_stock - $qty_alocation;
                        if ($final_stock < 0) {
                            DB::rollBack();
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Alokasi stock gagal, stock tidak mencukupi',
                                'data' => []
                            ], 400);
                        }

                        $master_stock_to = (int) getStock($product->stock_warehouse, $from_warehouse, $companyId, $item->product_id);
                        ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $to_warehouse)->where('company_id', $companyId)->delete();
                        ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $from_warehouse)->where('company_id', $companyId)->delete();
                        ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $to_warehouse, 'company_id' => $companyId]);
                        ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $from_warehouse, 'company_id' => $companyId]);

                        ProductStock::updateOrCreate(['product_id' => $item->product_id, 'company_id' => $companyId, 'warehouse_id' => $from_warehouse], [
                            'uid_inventory' => $inventory->uid_inventory,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $from_warehouse,
                            'stock' => $master_stock_to + $qty_alocation,
                            'is_allocated' => 1,
                            'company_id' => $inventory->company_id,
                        ]);

                        ProductStock::updateOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $to_warehouse, 'company_id' => $companyId], [
                            'uid_inventory' => $inventory->uid_inventory,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $to_warehouse,
                            'stock' => $master_stock - $qty_alocation,
                            'is_allocated' => 1,
                            'company_id' => $inventory->company_id,
                        ]);

                        // out
                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => null,
                            'warehouse_id' => $from_warehouse,
                            'company_id' => $inventory->company_id,
                            'type_product' => 'master',
                            'type_stock' => 'in',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => $item->qty_alocation,
                            'first_stock' => $master_stock,
                            'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                        ]);

                        saveLogStock([
                            'product_id' => $item->product_id,
                            'product_variant_id' => null,
                            'warehouse_id' => $to_warehouse,
                            'company_id' => $inventory->company_id,
                            'type_product' => 'master',
                            'type_stock' => 'out',
                            'type_transaction' => null,
                            'type_history' => 'transfer',
                            'name' => 'transfer product',
                            'qty' => $item->qty_alocation,
                            'first_stock' => $master_stock,
                            'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                        ]);

                        // update stock variant
                        $variants = ProductVariant::where('product_id', $item->product_id)->get();
                        foreach ($variants as $variant) {
                            ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $to_warehouse)->where('company_id', $companyId)->delete();
                            ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $from_warehouse)->where('company_id', $companyId)->delete();


                            // dd($product->stock_warehouse, $master_stock, $master_stock_to, $item->qty_alocation);
                            $current_stock = $master_stock - $item->qty_alocation; // 50 - 10 = 40
                            $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

                            $stockmarket =  floor($current_stock / $qty_bundling) ?? 0;
                            $stockmarket_to =  $master_stock_to + $item->qty_alocation;
                            ProductVariantStock::updateOrCreate([
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $to_warehouse,
                                'company_id' => $inventory->company_id,
                            ], [
                                'product_variant_id' => $variant->id,
                                'qty' => $current_stock,
                                'warehouse_id' => $to_warehouse,
                                'stock_of_market' => $stockmarket < 0 ? 0 : $stockmarket,
                                'company_id' => $inventory->company_id,
                            ]);

                            ProductVariantStock::updateOrCreate([
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $from_warehouse,
                                'company_id' => $inventory->company_id,
                            ], [
                                'product_variant_id' => $variant->id,
                                'qty' => $stockmarket_to,
                                'warehouse_id' => $from_warehouse,
                                'stock_of_market' => floor($stockmarket_to / $qty_bundling) ?? 0,
                                'company_id' => $inventory->company_id,
                            ]);

                            saveLogStock([
                                'product_id' => $item->product_id,
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $from_warehouse,
                                'company_id' => $inventory->company_id,
                                'type_product' => 'variant',
                                'type_stock' => 'in',
                                'type_transaction' => null,
                                'type_history' => 'transfer',
                                'name' => 'transfer product',
                                'qty' => floor($stockmarket_to / $qty_bundling) ?? 0,
                                'first_stock' => $master_stock,
                                'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                            ]);

                            saveLogStock([
                                'product_id' => $item->product_id,
                                'product_variant_id' => $variant->id,
                                'warehouse_id' => $to_warehouse,
                                'company_id' => $inventory->company_id,
                                'type_product' => 'variant',
                                'type_stock' => 'out',
                                'type_transaction' => null,
                                'type_history' => 'transfer',
                                'name' => 'transfer product',
                                'qty' => $stockmarket < 0 ? 0 : $stockmarket,
                                'first_stock' => $master_stock,
                                'description' => 'Purcase Order transfer - ' . $inventory->uid_inventory,
                            ]);
                        }

                        $purchase = PurchaseOrderItem::where([
                            'ref' => $inventory->uid_inventory,
                            'product_id' => $item->product_id,
                        ])->first();
                        if ($purchase) {
                            $purchase->update(['is_allocated' => 0]);
                        }
                    } else {

                        // $orderKonsiyasi = OrderTransfer::where('uid_lead', $inventory->uid_lead)->first(['id', 'master_bin_id']);

                        // //pengurangan stock
                        // $master_stock = (int) getStock($product->stock_warehouse, $item->from_warehouse_id);
                        // $bin_stocks = (int) getStock($product->stock_bins, $inventory->productTransfer->master_bin_id);
                        // ProductStock::where('product_id', $item->product_id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();
                        // ProductStock::create(['stock' => 0, 'product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId]);

                        // $qty_alocation = $item->qty_alocation;

                        // ProductStock::updateOrCreate(['product_id' => $item->product_id, 'warehouse_id' => $item->from_warehouse_id, 'company_id' => $companyId], [
                        //     'uid_inventory' => $inventory->uid_inventory,
                        //     'product_id' => $item->product_id,
                        //     'warehouse_id' => $item->from_warehouse_id,
                        //     'stock' => $master_stock + $qty_alocation,
                        //     'is_allocated' => 1,
                        //     'company_id' => $inventory->company_id,
                        // ]);

                        // // out
                        // saveLogStock([
                        //     'product_id' => $item->product_id,
                        //     'product_variant_id' => null,
                        //     'warehouse_id' => $item->from_warehouse_id,
                        //     'type_product' => 'master',
                        //     'type_stock' => 'out',
                        //     'type_transaction' => null,
                        //     'type_history' => 'transfer',
                        //     'name' => 'transfer product',
                        //     'qty' => $qty_alocation,
                        //     'description' => 'Konsiyasi transfer - ' . $inventory->uid_inventory,
                        // ]);

                        // // update stock variant
                        // $variants = ProductVariant::where('product_id', $item->product_id)->get();
                        // foreach ($variants as $variant) {
                        //     ProductVariantStock::where('product_variant_id', $variant->id)->where('warehouse_id', $item->from_warehouse_id)->where('company_id', $companyId)->delete();

                        //     $current_stock = $master_stock + $qty_alocation; // 50 - 10 = 40
                        //     $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;

                        //     $stockmarket =  floor($current_stock / $qty_bundling) ?? 0;
                        //     ProductVariantStock::updateOrCreate([
                        //         'product_variant_id' => $variant->id,
                        //         'warehouse_id' => $item->from_warehouse_id,
                        //         'company_id' => $inventory->company_id,
                        //     ], [
                        //         'product_variant_id' => $variant->id,
                        //         'qty' => $current_stock,
                        //         'warehouse_id' => $item->from_warehouse_id,
                        //         'stock_of_market' => $stockmarket < 0 ? 0 : $stockmarket,
                        //         'company_id' => $inventory->company_id,
                        //     ]);

                        //     MasterBinStock::updateOrCreate([
                        //         'master_bin_id' => $orderKonsiyasi->master_bin_id,
                        //         'product_variant_id' => $variant->id,
                        //     ], [
                        //         'master_bin_id' => $orderKonsiyasi->master_bin_id,
                        //         'product_id' => $item->product_id,
                        //         'product_variant_id' => $variant->id,
                        //         'company_id' => $inventory->company_id,
                        //         'stock' => floor($qty_alocation / $qty_bundling),
                        //         'description' => "Transfer Konsinyasi Cancel Barang"
                        //     ]);

                        //     saveLogStock([
                        //         'product_id' => $item->product_id,
                        //         'product_variant_id' => $variant->id,
                        //         'warehouse_id' => $item->from_warehouse_id,
                        //         'type_product' => 'variant',
                        //         'type_stock' => 'out',
                        //         'type_transaction' => null,
                        //         'type_history' => 'transfer',
                        //         'name' => 'transfer product',
                        //         'qty' => $qty_alocation,
                        //         'description' => 'Konsiyasi transfer - ' . $inventory->uid_inventory,
                        //     ]);
                        // }
                    }
                }
            }



            $message = $inventory->inventory_type == 'konsinyasi' ? 'Approval data item transfer konsinyasi berhasil dibatalkan!' : 'Approval data item transfer berhasil dibatalkan!';
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Proses alokasi gagal',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    public function inventoryAdjustmentCreate(Request $request)
    {
        try {
            DB::beginTransaction();
            $companyId = $request->account_id;
            $so_ethix = $request->so_ethix ?? $this->generateSONumber();

            $user = auth()->user();
            $role_type = $user->role->role_type;
            $sales = $user->id;
            if ($role_type == 'sales' && $request->sales) {
                $sales = $request->sales['value'];
            }

            $uid_inventory = $request->inventory_id ?? generateUid();
            // $inventory =  InventoryProductStock::where('reference_number', $request->po_number)->first();
            $dataTransfer = [
                'uid_inventory' => $uid_inventory,
                'uid_lead' => $uid_inventory,
                'allocated_by' => auth()->user()->id,
                'warehouse_id' => $request->warehouse_id,
                'destination_warehouse_id' => $request->to_warehouse_id,
                'product_id' => @$request->product_id,
                'reference_number' => @$request->reference_number,
                'created_by' => $request->created_by ?? $user->id,
                'vendor' => $request->vendor,
                'inventory_status' => 'draft',
                'inventory_type' => $request->inventory_type,
                'status' => 'draft',
                'received_date' => date('Y-m-d'),
                'note' => $request->note,
                'company_id' => $companyId,
                'so_ethix' => $so_ethix,
                'post_ethix' => $request->post_ethix,
                'transfer_category' => $request->transfer_category ?? 'new',
                'is_konsinyasi' => '0',
                'master_bin_id' => $request->master_bin_id
            ];

            $inventory = InventoryProductStock::updateOrCreate(['uid_inventory' => $uid_inventory], $dataTransfer);

            if ($request->inventory_type == 'adjustment') {
                $so_number = $request->order_number ?? OrderTransfer::generateOrderNumber(6);
                $si_number =  $request->invoice_number ?? OrderTransfer::generateInvoiceNumber(6);

                $tax_id =  $companyId == 1 ? 1 : null;
                foreach ($request->itemkons as $key => $item) {
                    $product = Product::find($item['product_id']);
                    InventoryDetailItem::updateOrCreate(['id' => $item['id']], [
                        'uid_inventory' => $inventory->uid_inventory,
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'qty_alocation' => $item['qty'],
                        'from_warehouse_id' => $inventory->warehouse_id,
                        'to_warehouse_id' => $request->to_warehouse_id,
                        'master_bin_id' => $inventory->master_bin_id,
                        'sku' => $product->sku,
                        'u_of_m' => $product->u_of_m,
                        'tax_id' => $item['tax_id'] ?? $tax_id,
                        'tax_amount' => $item['tax_amount'],
                        'tax_percentage' => $item['tax_percentage'],
                        'discount_percentage' => $item['discount_percentage'],
                        'discount' => $item['discount'],
                        'discount_amount' => $item['discount_amount'],
                        'subtotal' => $item['subtotal'],
                        'price_nego' => $item['price_nego'],
                        'total' => $item['total'],
                        'stock_awal' => @$item['stock'] ?? @$item['stock_awal'],
                    ]);
                }
                // UpdatePriceQueue::dispatch($order, 'order_transfers')->onQueue('queue-prod');
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan',
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Gagal Disimpan',
                'data' => $th->getMessage()
            ], 400);
        }
    }

    public function getTrfNumber()
    {
        return response()->json(['value' => $this->generateSONumber()]);
    }

    public function getSONumber()
    {
        return response()->json(['value' => $this->generateSOKNumber()]);
    }

    public function getSINumber()
    {
        return response()->json(['value' => $this->generateDONumber()]);
    }

    public function inventoryTransferUpdate(Request $request, $inventory_id)
    {
        $inventory = InventoryProductStock::find($inventory_id);
        $inventory->update([
            'uid_inventory' => hash('crc32', Carbon::now()->format('U')),
            'allocated_by' => auth()->user()->id,
            'warehouse_id' => $request->from_warehouse_id,
            'destination_warehouse_id' => $request->to_warehouse_id,
            'reference_number' => $request->po_number,
            'created_by' => $request->created_by,
            'vendor' => $request->vendor,
            'inventory_status' => 'alocated',
            'inventory_type' => 'transfer',
            'status' => 'done',
            'received_date' => date('Y-m-d'),
            'note' => $request->note,
        ]);

        foreach ($request->items as $item) {
            StockAllocationHistory::create([
                'uid_inventory' => $inventory->uid_inventory,
                'product_id' => $item['product_id'],
                'quantity' => $item['qty'],
                'from_warehouse_id' => $item['from_warehouse_id'],
                'to_warehouse_id' => $item['to_warehouse_id'],
                'sku' => $item['sku'],
                'u_of_m' => $item['u_of_m'],
                'transfer_date' => date('Y-m-d'),
            ]);

            ProductStock::where([
                'uid_inventory' => $inventory->uid_inventory,
                'product_variant_id' => $item['product_id']
            ])->update([
                'warehouse_id' => $request['to_warehouse_id'],
            ]);
        }

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Update Transfer Inventory - ' . $inventory_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryStockDelete($inventory_id)
    {
        $inventory = InventoryProductStock::where('uid_inventory', $inventory_id)->first();
        $inventory->items()->delete();
        $inventory->delete();

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Delete Stock Inventory - ' . $inventory_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    // inventory return
    public function inventoryReturnOld(Request $request)
    {
        $search = $request->search;
        $date = $request->date;
        $account_id = $request->account_id;
        $inventory =  InventoryProductReturn::query()->with('items');
        if ($search) {
            $inventory->where('status', 'like', "%$search%");
            $inventory->orWhere('vendor', 'like', "%$search%");
            $inventory->orWhere('nomor_sr', 'like', "%$search%");
            $inventory->orWhereHas('userCreated', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($date) {
            $inventory->whereBetween('received_date', $date);
        }

        // cek switch account
        if ($account_id) {
            $inventory->where('company_id', $account_id);
        }

        $inventories =  $inventory->orderBy('inventory_product_returns.created_at', 'desc')->paginate($request->perpage);


        return response()->json([
            'status' => 'success',
            'data' => $inventories
        ]);
    }

    public function inventoryReturn(Request $request)
    {
        $search = $request->search;
        $date = $request->date;
        $account_id = $request->account_id;

        $inventory = DB::table('inventory_product_returns as a')
            ->select('a.id', 'a.uid_inventory', 'a.nomor_sr', 'a.vendor', 'a.status', 'a.received_date', 'e.name as warehouse_name', 'c.account_name as company_account_name', 'd.name as created_by_name')
            ->join('inventory_items as b', 'a.uid_inventory', '=', 'b.uid_inventory')
            ->join('company_accounts as c', 'a.company_id', '=', 'c.id')
            ->join('users as d', 'a.created_by', '=', 'd.id')
            // ->join('vendors as f', 'a.vendor', '=', 'd.vendor_code')
            ->join('warehouses as e', 'a.warehouse_id', '=', 'e.id');

        if ($search) {
            $inventory->where('a.status', 'like', "%$search%");
            $inventory->orWhere('a.vendor', 'like', "%$search%");
            $inventory->orWhere('a.nomor_sr', 'like', "%$search%");
            $inventory->orWhere('d.name', 'like', "%$search%");
            $inventory->orWhere('e.name', 'like', "%$search%");
        }

        if ($date) {
            $inventory->whereBetween('a.received_date', $date);
        }

        // cek switch account
        if ($account_id) {
            $inventory->where('a.company_id', $account_id);
        }

        $inventories =  $inventory->orderBy('a.created_at', 'desc')->groupBy('a.uid_inventory')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $inventories
        ]);
    }

    public function inventoryReturnDetail($inventory_id)
    {
        $inventory = InventoryProductReturn::with(['items', 'itemPreReceived', 'itemReceived'])->where('uid_inventory', $inventory_id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryReturnCreate(Request $request)
    {

        $companyId = auth()->user()->company_id ?? $request->account_id ?? $request->company_account_id;


        $inventory = InventoryProductReturn::updateOrCreate(['nomor_sr' => $request->nomor_sr], [
            'uid_inventory' => hash('crc32', Carbon::now()->format('U')),
            'warehouse_id' => $request->warehouse_id,
            'nomor_sr' => $request->nomor_sr,
            'transaction_channel' => $request->transaction_channel,
            'barcode' => $request->barcode,
            'expired_date' => $request->expired_date,
            'company_account_id' => $request->company_account_id,
            'created_by' => auth()->user()->id,
            'vendor' => $request->vendor,
            'status' => 2,
            'received_date' => $request->received_date,
            'note' => $request->note,
            'company_id' => $companyId,
            'case_type' => $request->case_type,
            'case_title' => $request->case_title,
            'type_return' => $request->type_return,
        ]);

        $productItems = [];
        foreach ($request->items as $item) {
            $product = $request->type_return == 'po' ? Product::find($item['product_id']) : ProductVariant::find($item['product_id']);
            $productItems[] = [
                "product_code" => $product->sku,
                "product_name" => $product->name,
                "quantity" => $item['qty_alocation'],
                "unit_price" => $product->price['final_price'],
                "weight" => 1
            ];
            $inventory->items()->create([
                'product_id' => $item['product_id'],
                'price' => 0,
                'qty' => $item['qty_alocation'],
                'subtotal' => 0,
                'type' => 'return',
                'sku' => $item['sku'],
                'u_of_m' => $item['u_of_m'],
                'notes' => $item['notes'] ?? null,
                'is_master' => 1,
            ]);
        }

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Create Retur Inventory - ' . $inventory->uid_inventory,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        try {
            $company = CompanyAccount::find($companyId, ['account_code']);
            $log = OrderSubmitLog::create([
                'submited_by' => auth()->user()->id,
                'type_si' => 'submit-return-ethix',
                'vat' => 0,
                'tax' => 0,
                'ref_id' => $inventory->id,
                'company_id' => $company->account_code
            ]);
            $headers = [
                'secretcode: ' . getSetting('ETHIX_SECRETCODE_' . $company->account_code),
                'secretkey: ' . getSetting('ETHIX_SECRETKEY_' . $company->account_code),
                'Content-Type: application/json'
            ];

            $curl_post_data = array(
                "client_code" => getSetting('ETHIX_CLIENTCODE_' . $company->account_code),
                "location_code" => $inventory->warehouse->ethix_id,
                "courier_name" => "ANTER AJA",
                "delivery_type" => "REGULER",
                "order_type" => "RTV",
                "order_date" => "2023-08-23",
                "order_code" => $request->nomor_sr,
                "channel_origin_name" => "FIS",
                "payment_date" => "2023-08-23 19:29:00",
                "is_cashless" => true,
                "recipient_name" => "Fikar",
                "recipient_phone" => "08888888",
                "recipient_subdistrict" => "Ciputat",
                "recipient_district" => "Cipayung",
                "recipient_city" => "Tangsel",
                "recipient_province" => "Banten",
                "recipient_country" => "Indonesia",
                "recipient_address" => "jalan darat",
                "recipient_postal_code" => "12270",
                "Agent" => "Vidi",
                "product_price" => "20000",
                "product_discount" => "",
                "shipping_price" => "",
                "shipping_discount" => "",
                "insurance_price" => "",
                "total_price" => "14750000",
                "total_weight" => "1",
                "total_koli" => "1",
                "cod_price" => "0",
                "product_discount" => "0",
                "shipping_price" => "9000",
                "shipping_discount" => "0",
                "insurance_price" => "0",
                "created_via" => "FIS System",
                "is_quarantine" => true,
                "product_information" => $productItems,
            );

            setSetting('sales_return_ethix_body', json_encode($curl_post_data));

            $url = 'https://wms.ethix.id/index.php?r=Externalv2/Order/PostOrder';
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $url);
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_TIMEOUT, 9000);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
            $responseData = curl_exec($handle);
            curl_close($handle);

            setSetting('response_return', json_encode($responseData));
            $responseJSON = json_decode($responseData, true);
            if (!$responseJSON && is_string($responseData)) {
                // $inventory->update(['status_ethix_submit' => 'needsubmited']);
                $log->update(['body' => json_encode($curl_post_data)]);
                OrderSubmitLogDetail::updateOrCreate([
                    'order_submit_log_id' => $log->id,
                    'order_id' => $inventory->id
                ], [
                    'order_submit_log_id' => $log->id,
                    'order_id' => $inventory->id,
                    'status' => 'failed',
                    'error_message' => $responseData
                ]);
                return;
            }

            // Check if any error occured
            if (curl_errno($handle)) {
                $log->update(['body' => json_encode($curl_post_data)]);
                // $inventory->update(['status_ethix_submit' => 'needsubmited']);
                return;
            }

            if (isset($responseJSON['status'])) {
                if (in_array($responseJSON['status'], [200, 201])) {
                    // $inventory->update(['status_ethix_submit' => 'submited']);
                    $log->update(['body' => null]);
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $log->id,
                        'order_id' => $inventory->id
                    ], [
                        'order_submit_log_id' => $log->id,
                        'order_id' => $inventory->id,
                        'status' => 'success',
                        'error_message' => 'SUCCESS SUBMIT ETHIX'
                    ]);
                } else {
                    // $inventory->update(['status_ethix_submit' => 'needsubmited']);
                    $log->update(['body' => json_encode($curl_post_data)]);
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $log->id,
                        'order_id' => $inventory->id
                    ], [
                        'order_submit_log_id' => $log->id,
                        'order_id' => $inventory->id,
                        'status' => 'failed',
                        'error_message' => $responseJSON['message']
                    ]);
                }
            }
        } catch (\Throwable $th) {
            setSetting('response_return_error', $th->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryReturnPreReceived(Request $request, $uid_inventory)
    {
        try {
            DB::beginTransaction();
            $inventory = InventoryProductReturn::where('uid_inventory', $uid_inventory)->first();
            // $item = $inventory->items()->where('product_id', $request->product_id)->first();
            $product = ProductVariant::find($request->product_id);
            $inventory->items()->create([
                'product_id' => $request->product_id,
                'price' => 0,
                'qty' => $request->qty,
                'qty_diterima' => $request->qty_diterima,
                'subtotal' => 0,
                'type' => 'return-prcved',
                'sku' => $product->sku,
                'u_of_m' => $product->u_of_m,
                'case_return' => null,
                'notes' => $request->notes ?? null,
                'is_master' => 0,
            ]);

            $dataLog = [
                'log_type' => '[fis-dev]inventory',
                'log_description' => 'Return PreReceived Inventory - ' . $uid_inventory,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $inventory
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function updateStatusReceivedVendor(Request $request, $inventory_item_id)
    {
        try {
            DB::beginTransaction();
            $item = InventoryItem::find($inventory_item_id);
            if ($request->received_vendor == 2) {
                $variant = ProductVariant::find($item->product_id);
                $row = InventoryProductReturn::where('uid_inventory', $item->uid_inventory)->first();

                if ($variant) {
                    // if ($variant->is_bundling > 0) {
                    //     $bundlings = ProductVariantBundling::where('product_variant_id', $variant->id)->get();
                    //     foreach ($bundlings as $key => $bundling) {
                    //         $product = Product::find($bundling->product_id);
                    //         $master_stock = (int) getStock($product->stock_warehouse, $row->warehouse_id, $row->company_id, $product->id);
                    //         $qty_bundling = $bundling->product_qty > 0 ? $bundling->product_qty : 1;
                    //         $variants = ProductVariant::where('product_id', $bundling->product_id)->get();
                    //         $total_qty_master = $request->qty_diterima * $qty_bundling;
                    //         $current_stock = $master_stock + $total_qty_master;
                    //         foreach ($variants as $variant_item) {
                    //             $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
                    //             $data_stock = [
                    //                 'product_variant_id'  => $variant_item->id,
                    //                 'warehouse_id'  => $row->warehouse_id,
                    //                 'qty'  => $current_stock,
                    //                 'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
                    //                 'company_id' => $row->company_id,
                    //             ];
                    //             ProductVariantStock::updateOrCreate([
                    //                 'product_variant_id'  => $variant_item->id,
                    //                 'warehouse_id'  => $row->warehouse_id,
                    //                 'company_id' => $row->company_id,
                    //             ], $data_stock);
                    //             saveLogStock([
                    //                 'product_id' => $variant_item->product_id,
                    //                 'product_variant_id' => $variant_item->id,
                    //                 'company_id' => $row->company_id,
                    //                 'warehouse_id' => $row->warehouse_id,
                    //                 'type_product' => 'variant',
                    //                 'type_stock' => 'in',
                    //                 'type_transaction' => null,
                    //                 'type_history' => 'return',
                    //                 'name' => 'return product',
                    //                 'qty' => floor($current_stock / $qty_bundling_variant),
                    //                 'first_stock' => $master_stock,
                    //                 'description' => 'Inventory return 2 - ' . $row->uid_inventory,
                    //             ]);
                    //         }

                    //         $total_qty = $request->qty_diterima * $qty_bundling;
                    //         $data_stock_1 = [
                    //             'uid_inventory'  => $row->uid_inventory,
                    //             'warehouse_id'  => $row->warehouse_id,
                    //             'product_id'  => $bundling->product_id,
                    //             'stock'  => $master_stock + $total_qty,
                    //             'ref' => $row->uid_inventory,
                    //             'company_id' => $row->company_id,
                    //             'is_allocated' => 1,
                    //         ];
                    //         ProductStock::updateOrCreate([
                    //             'warehouse_id'  => $row->warehouse_id,
                    //             'product_id'  => $bundling->product_id,
                    //             'company_id' => $row->company_id,
                    //         ], $data_stock_1);
                    //         saveLogStock([
                    //             'product_id' => $bundling->product_id,
                    //             'product_variant_id' => null,
                    //             'warehouse_id' => $row->warehouse_id,
                    //             'company_id' => $row->company_id,
                    //             'type_product' => 'master',
                    //             'type_stock' => 'in',
                    //             'type_transaction' => null,
                    //             'type_history' => 'return',
                    //             'name' => 'return product',
                    //             'qty' => $total_qty,
                    //             'first_stock' => $master_stock,
                    //             'description' => 'Inventory return 2 - ' . $row->uid_inventory,
                    //         ]);
                    //     }
                    // } else {
                    $product = Product::find($variant->product_id);
                    $master_stock = (int) getStock($product->stock_warehouse, $row->warehouse_id, $row->company_id, $product->id);
                    $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
                    $variants = ProductVariant::where('product_id', $variant->product_id)->get();
                    $total_qty_master = $request->qty_diterima * $qty_bundling;
                    $current_stock = $master_stock + $total_qty_master;
                    foreach ($variants as $variant_item) {
                        // $master_stock = (int) getStock($variant->stock_warehouse, $row->warehouse_id,$row->company_id,$product->id);
                        $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
                        $data_stock = [
                            'product_variant_id'  => $variant_item->id,
                            'warehouse_id'  => $row->warehouse_id,
                            'qty'  => $current_stock,
                            'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
                            'company_id' => $row->company_id,
                        ];
                        ProductVariantStock::updateOrCreate([
                            'product_variant_id'  => $variant_item->id,
                            'warehouse_id'  => $row->warehouse_id,
                            'company_id' => $row->company_id,
                        ], $data_stock);
                        saveLogStock([
                            'product_id' => $variant_item->product_id,
                            'product_variant_id' => $variant_item->id,
                            'company_id' => $row->company_id,
                            'warehouse_id' => $row->warehouse_id,
                            'type_product' => 'variant',
                            'type_stock' => 'in',
                            'type_transaction' => null,
                            'type_history' => 'return',
                            'name' => 'return product',
                            'qty' => floor($current_stock / $qty_bundling_variant),
                            'first_stock' => $master_stock,
                            'description' => 'Inventory return 2 - ' . $row->uid_inventory,
                        ]);
                    }

                    $total_qty = $request->qty_diterima * $qty_bundling;
                    $data_stock_1 = [
                        'uid_inventory'  => $row->uid_inventory,
                        'warehouse_id'  => $row->warehouse_id,
                        'product_id'  => $variant->product_id,
                        'stock'  => $master_stock + $total_qty,
                        'ref' => $row->uid_inventory,
                        'company_id' => $row->company_id,
                        'is_allocated' => 1,
                    ];
                    ProductStock::updateOrCreate([
                        'warehouse_id'  => $row->warehouse_id,
                        'product_id'  => $variant->product_id,
                        'company_id' => $row->company_id,
                    ], $data_stock_1);
                    saveLogStock([
                        'product_id' => $variant->product_id,
                        'product_variant_id' => null,
                        'warehouse_id' => $row->warehouse_id,
                        'company_id' => $row->company_id,
                        'type_product' => 'master',
                        'type_stock' => 'in',
                        'type_transaction' => null,
                        'type_history' => 'return',
                        'name' => 'return product',
                        'qty' => $total_qty,
                        'first_stock' => $master_stock,
                        'description' => 'Inventory return 2 - ' . $row->uid_inventory,
                    ]);
                    // }
                }
            }

            $item->update([
                'received_vendor' => $request->received_vendor,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Success update status received vendor',

            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed update status received vendor',
                'data' => $th->getMessage()
            ]);
        }
    }

    public function inventoryReturnReceived(Request $request, $uid_inventory)
    {
        try {
            DB::beginTransaction();
            $row = InventoryProductReturn::where('uid_inventory', $uid_inventory)->first();

            $detail = InventoryItem::where('uid_inventory', $uid_inventory)->where('product_id', $request->product_id)->first();
            $variant = ProductVariant::find($request->product_id);

            InventoryItem::create([
                'uid_inventory' => $uid_inventory,
                'product_id' => $request->product_id,
                'qty' => $detail->qty,
                'price' => 0,
                'sku' => $request->sku,
                'u_of_m' => $request->u_of_m,
                'type' => 'return-received',
                'case_return' => $detail->case_return,
                'qty_diterima' => $request->qty_diterima,
                'notes' => $request->notes ?? null,
                'ref' => $uid_inventory,
                'received_number' => $this->generateRecNumber($uid_inventory),
            ]);

            // if ($variant->is_bundling > 0) {
            //     $bundlings = ProductVariantBundling::where('product_variant_id', $variant->id)->get();
            //     foreach ($bundlings as $key => $bundling) {
            //         $product = Product::find($bundling->product_id);
            //         $master_stock = (int) getStock($product->stock_warehouse, $row->warehouse_id, $row->company_id, $product->id);
            //         $qty_bundling = $bundling->product_qty > 0 ? $bundling->product_qty : 1;
            //         $variants = ProductVariant::where('product_id', $bundling->product_id)->get();
            //         $total_qty_master = $request->qty_diterima * $qty_bundling;
            //         $current_stock = $master_stock + $total_qty_master;
            //         foreach ($variants as $variant_item) {
            //             // $master_stock = (int) getStock($variant->stock_warehouse, $row->warehouse_id,$row->company_id,$product->id);
            //             $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
            //             $data_stock = [
            //                 'product_variant_id'  => $variant_item->id,
            //                 'warehouse_id'  => $row->warehouse_id,
            //                 'qty'  => $current_stock,
            //                 'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
            //                 'company_id' => $row->company_id,
            //             ];
            //             ProductVariantStock::updateOrCreate([
            //                 'product_variant_id'  => $variant_item->id,
            //                 'warehouse_id'  => $row->warehouse_id,
            //                 'company_id' => $row->company_id,
            //             ], $data_stock);
            //             saveLogStock([
            //                 'product_id' => $variant_item->product_id,
            //                 'product_variant_id' => $variant_item->id,
            //                 'warehouse_id' => $row->warehouse_id,
            //                 'company_id' => $row->company_id,
            //                 'type_product' => 'variant',
            //                 'type_stock' => 'in',
            //                 'type_transaction' => null,
            //                 'type_history' => 'return',
            //                 'name' => 'return product',
            //                 'qty' => floor($current_stock / $qty_bundling_variant),
            //                 'first_stock' => $master_stock,
            //                 'description' => 'Inventory return - ' . $row->uid_inventory,
            //             ]);
            //         }

            //         $total_qty = $request->qty_diterima * $qty_bundling;
            //         $data_stock_1 = [
            //             'uid_inventory'  => $uid_inventory,
            //             'warehouse_id'  => $row->warehouse_id,
            //             'product_id'  => $bundling->product_id,
            //             'stock'  => $master_stock + $total_qty,
            //             'ref' => $uid_inventory,
            //             'company_id' => $row->company_id,
            //             'is_allocated' => 1,
            //         ];
            //         ProductStock::updateOrCreate([
            //             'warehouse_id'  => $row->warehouse_id,
            //             'product_id'  => $bundling->product_id,
            //             'company_id' => $row->company_id,
            //         ], $data_stock_1);
            //         saveLogStock([
            //             'product_id' => $bundling->product_id,
            //             'product_variant_id' => null,
            //             'warehouse_id' => $row->warehouse_id,
            //             'company_id' => $row->company_id,
            //             'type_product' => 'master',
            //             'type_stock' => 'in',
            //             'type_transaction' => null,
            //             'type_history' => 'return',
            //             'name' => 'return product',
            //             'qty' => $total_qty,
            //             'first_stock' => $master_stock,
            //             'description' => 'Inventory return - ' . $row->uid_inventory,
            //         ]);
            //     }
            // } else {
            $product = Product::find($variant->product_id);
            $master_stock = (int) getStock($product->stock_warehouse, $row->warehouse_id, $row->company_id, $product->id);
            $qty_bundling = $variant->qty_bundling > 0 ? $variant->qty_bundling : 1;
            $variants = ProductVariant::where('product_id', $variant->product_id)->get();
            $total_qty_master = $request->qty_diterima * $qty_bundling;
            $current_stock = $master_stock + $total_qty_master;
            foreach ($variants as $variant_item) {
                // $master_stock = (int) getStock($variant->stock_warehouse, $row->warehouse_id);
                $qty_bundling_variant = $variant_item->qty_bundling > 0 ? $variant_item->qty_bundling : 1;
                $data_stock = [
                    'product_variant_id'  => $variant_item->id,
                    'warehouse_id'  => $row->warehouse_id,
                    'qty'  => $current_stock,
                    'stock_of_market'  => floor($current_stock / $qty_bundling_variant),
                    'company_id' => $row->company_id,
                ];

                ProductVariantStock::updateOrCreate([
                    'product_variant_id'  => $variant_item->id,
                    'warehouse_id'  => $row->warehouse_id,
                    'company_id' => $row->company_id,
                ], $data_stock);

                saveLogStock([
                    'product_id' => $variant_item->product_id,
                    'product_variant_id' => $variant_item->id,
                    'warehouse_id' => $row->warehouse_id,
                    'company_id' => $row->company_id,
                    'type_product' => 'variant',
                    'type_stock' => 'in',
                    'type_transaction' => null,
                    'type_history' => 'return',
                    'name' => 'return product',
                    'qty' => floor($current_stock / $qty_bundling_variant),
                    'first_stock' => $master_stock,
                    'description' => 'Inventory return - ' . $row->uid_inventory,
                ]);
            }

            $total_qty = $request->qty_diterima * $qty_bundling;
            $data_stock_1 = [
                'uid_inventory'  => $uid_inventory,
                'warehouse_id'  => $row->warehouse_id,
                'product_id'  => $variant->product_id,
                'stock'  => $master_stock + $total_qty,
                'ref' => $uid_inventory,
                'company_id' => $row->company_id,
                'is_allocated' => 1,
            ];
            ProductStock::updateOrCreate([
                'warehouse_id'  => $row->warehouse_id,
                'product_id'  => $variant->product_id,
                'company_id' => $row->company_id,
            ], $data_stock_1);
            saveLogStock([
                'product_id' => $variant->product_id,
                'product_variant_id' => null,
                'warehouse_id' => $row->warehouse_id,
                'company_id' => $row->company_id,
                'type_product' => 'master',
                'type_stock' => 'in',
                'type_transaction' => null,
                'type_history' => 'return',
                'name' => 'return product',
                'qty' => $total_qty,
                'first_stock' => $master_stock,
                'description' => 'Inventory return - ' . $row->uid_inventory,
            ]);
            // }

            $row->update(['status' => 3]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function inventoryReturnUpdate(Request $request, $inventory_id)
    {
        $inventory = InventoryProductReturn::where('uid_inventory', $inventory_id)->first();
        $inventory->update([
            'warehouse_id' => $request->warehouse_id,
            'nomor_sr' => $request->nomor_sr,
            'transaction_channel' => $request->transaction_channel,
            'barcode' => $request->barcode,
            'expired_date' => $request->expired_date,
            'created_by' => auth()->user()->id,
            'vendor' => $request->vendor,
            'status' => $request->status ?? 'draft',
            'received_date' => $request->received_date,
            'note' => $request->note,
        ]);

        $inventory->items()->delete();
        foreach ($request->items as $item) {
            $inventory->items()->create([
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'subtotal' => $item['sub_total'],
                'type' => 'return'
            ]);
        }

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Return Update Inventory - ' . $inventory_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryReturnDelete($inventory_id)
    {
        $inventory = InventoryProductReturn::where('uid_inventory', $inventory_id)->first();
        $inventory->items()->delete();
        $inventory->delete();

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Return Delete Inventory - ' . $inventory_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function inventoryReturnVerify(Request $request, $inventory_id)
    {
        $inventory = InventoryProductReturn::where('uid_inventory', $inventory_id)->first();
        $inventory->update([
            'status' => $request->status,
            'rejected_reason' => $request->rejected_reason,
        ]);

        // if ($inventory->type_return == 'so') {
        //     try {
        //         DB::beginTransaction();
        //         $order = DB::table('order_manuals as a')->where('a.order_number', $inventory->case_title)->select('a.id', 'a.uid_lead')->first();

        //         if ($order) {
        //             foreach ($inventory->items as $key => $value) {
        //                 $order_delivery = DB::table('order_deliveries as b')
        //                     ->where('b.uid_lead', $order->uid_lead)
        //                     ->where('d.product_id', $value->product_id)
        //                     ->join('product_needs as c', 'b.product_need_id', 'c.id')
        //                     ->join('product_variants as d', 'c.product_id', 'd.id')
        //                     ->select('b.id', 'b.qty_delivered')
        //                     ->first();

        //                 if ($order_delivery) {
        //                     $qty_delivered = $order_delivery->qty_delivered - $value->qty;
        //                     DB::table('order_deliveries as b')->where('id', $order_delivery->id)->update(['qty_delivered' => $qty_delivered > 0 ? $qty_delivered : $order_delivery->qty_delivered]);
        //                 }
        //             }
        //         }
        //         DB::commit();
        //     } catch (\Throwable $th) {
        //         DB::rollBack();
        //         setSetting('return_failed', $th->getMessage());
        //     }
        // }

        // if ($request->status == 2) {
        //     foreach ($inventory->items as $item) {
        //         // product stock
        //         $product = ProductVariant::find($item->product_id);
        //         $data_stock = [
        //             'uid_inventory'  => $inventory->uid_inventory,
        //             'warehouse_id'  => $inventory->warehouse_id,
        //             'product_id'  => $product->product_id,
        //             'stock'  => -$item->qty,
        //             'ref' => $inventory->uid_inventory,
        //             'company_id' => $inventory->company_id,
        //             'is_allocated' => 1
        //         ];
        //         ProductStock::create($data_stock);
        //     }
        // }

        $dataLog = [
            'log_type' => '[fis-dev]inventory',
            'log_description' => 'Return Verify Inventory - ' . $inventory_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => $request->status == 2 ? 'Inventory Return has been approved' : 'Inventory Return has been rejected',
            'data' => $inventory
        ]);
    }

    public function inventoryReturnComplete(Request $request, $uid_inventory)
    {
        try {
            DB::beginTransaction();
            $row = InventoryProductReturn::where('uid_inventory', $uid_inventory)->first();
            $row->update(['status' => 1]);
            setSetting('order_return_row', json_encode($row));
            if ($row->type_return == 'so') {
                try {
                    DB::beginTransaction();
                    $order = DB::table('order_manuals as a')->where('a.order_number', $row->case_title)->select('a.id', 'a.uid_lead')->first();
                    if ($order) {
                        foreach ($row->items as $key => $value) {
                            $order_delivery = DB::table('order_deliveries as b')
                                ->where('b.uid_lead', $order->uid_lead)
                                ->where('b.status', '!=', 'cancel')
                                ->where('d.id', $value->product_id)
                                ->join('product_needs as c', 'b.product_need_id', 'c.id')
                                ->join('product_variants as d', 'c.product_id', 'd.id')
                                ->select('b.id', 'b.qty_delivered')
                                ->first();
                            if ($order_delivery) {
                                $qty_delivered = $order_delivery->qty_delivered - $value->qty;
                                $qty_return = $order_delivery->qty_return + $value->qty;
                                DB::table('order_deliveries')->where('id', $order_delivery->id)->update(['qty_delivered' => $qty_delivered, 'qty_return' => $qty_return]);
                            }
                        }
                    }
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    setSetting('return_failed', $th->getMessage());
                }
            }

            $dataLog = [
                'log_type' => '[fis-dev]inventory',
                'log_description' => 'Return Complete Inventory - ' . $uid_inventory,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    private function generateRefNumber($warehouse = 'FLIMTY')
    {
        $data = DB::select("SELECT * FROM `tbl_inventory_product_stocks` order by id desc limit 0,1");
        $count_code = 8;
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->reference_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'WH/' . $warehouse . '/' . $next;
            }
        } else {
            $nomor = 'WH/' . $warehouse . '/' . '00000001';
        }
        return $nomor;
    }

    private function generateRecNumber($uid_inventory)
    {
        $data = InventoryItem::where('uid_inventory', $uid_inventory)->orderBy('id', 'desc')->first();
        $count_code = 8;
        $nomor = 'REC/' . date('Y') . '/00000001';
        if ($data) {
            $awal = substr($data->received_number, -$count_code);
            $next = sprintf("%08d", ((int)$awal + 1));
            $nomor = 'REC/' . date('Y') . '/' . $next;
        }

        return $nomor;
    }

    private function generatePrNumber()
    {
        $rw = InventoryProductReturn::orderBy('id', 'desc')->limit(1)->first();
        $date = date('m/Y');
        $nomor = 'STCKRTRN/' . $date . '/' . '00001';
        $count_code = 5;
        if ($rw) {
            $awal = substr($rw->nomor_sr, -$count_code);
            $next = sprintf("%05d", ((int)$awal + 1));
            $nomor = 'STCKRTRN/' . $date . '/' . $next;
        }
        return $nomor;
    }

    public function export_transfer(Request $request)
    {
        $file_name = 'convert/FIS-Product_Transfer-' . date('d-m-Y') . '.xlsx';

        Excel::store(new ProductTransferExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function export_received()
    {
        $product = InventoryProductStock::query();
        // echo"<pre>";print_r($product->get());die();
        $file_name = 'convert/FIS-Product_Received-' . date('d-m-Y') . '.xlsx';

        Excel::store(new ProductReceivedExport($product), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function export_return()
    {
        $product = InventoryProductStock::query();

        $file_name = 'convert/FIS-Product_Return-' . date('d-m-Y') . '.xlsx';

        Excel::store(new ProductReturnExport($product), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function generateSONumber()
    {
        $lastPo = InventoryProductStock::whereNotNull('so_ethix')->orderBy('id', 'desc')->first();
        $number = '0001';
        if ($lastPo) {
            $number = substr($lastPo->so_ethix, -4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        }
        return 'TF/FIS/WAREHOUSE/' . $number;
    }

    public function generateDONumber()
    {
        $lastPo = PurchaseOrderItem::whereNotNull('do_number')->orderBy('id', 'desc')->first();
        if ($lastPo) {
            $number = substr($lastPo->do_number, -4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        } else {
            $number = '0001';
        }
        return 'DO/' . date('Y') . '/' . $number;
    }

    public function generateSOKNumber()
    {
        // $username = auth()->user()->username ?? 99;
        $year = date('Y');
        // $order_number = 'SO/' . $year . '/2';
        // $nomor = 'SO/' . $year . '/2' . $username . '000001';
        // $rw = OrderTransfer::whereNotNull('order_number')->orderBy('id', 'desc')->orderBy('order_number', 'desc')->first(['order_number']);
        // if ($rw) {
        //     $awal = substr($rw->order_number, -6);
        //     $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
        //     $nomor = 'SO/' . $year . '/' . $next;

        //     $row = OrderTransfer::where('order_number', $nomor)->first(['order_number']);
        //     if ($row) {
        //         $nomor = 'SO/' . $year . '/' . $next + 1;
        //     }
        //     return $nomor;
        // }
        $nomor = OrderTransfer::generateOrderNumber(6);
        return $nomor;
    }

    public function generateSIKNumber()
    {
        $year = date('Y');
        // $username = auth()->user()->username ?? 99;
        // $nomor = 'SJ/' . $year . '/2' . $username . '000001';
        // $rw = OrderTransfer::whereNotNull('invoice_number')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first();

        // if ($rw) {
        //     $awal = substr($rw->invoice_number, -6);
        //     $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
        //     $nomor = 'SJ/' . $year . '/2' . $next;
        // }
        $nomor = OrderTransfer::generateInvoiceNumber(6);
        return $nomor;
    }

    public function importOrder(Request $request)
    {
        try {
            DB::beginTransaction();
            if (!$request->hasFile('file')) {
                return response()->json([
                    'data' => [],
                    'message' => 'File tidak diupload'
                ], 400);
            }

            $file = $request->file('file');
            if (!$file->isValid()) {
                return response()->json([
                    'data' => [],
                    'message' => 'File tidak valid'
                ], 400);
            }

            $user = auth()->user();
            $data = Excel::toArray([], $file);
            $headers = $data[0][0];

            // Ambil data setelah header
            $rows = array_slice($data[0], 1);

            // Pemetaan data berdasarkan header
            $mappedData = [];
            foreach ($rows as $row) {
                $mappedRow = [];
                foreach ($headers as $key => $header) {
                    $mappedRow[$header] = $row[$key];
                }

                $codeSO = $mappedRow['Code TF'];

                // Jika Code SO sudah ada, tambahkan item ke dalam array 'items'
                if (isset($mappedData[$codeSO])) {
                    $mappedData[$codeSO]['items'][] = $mappedRow;
                } else {
                    // Jika Code SO belum ada, buat array baru dengan 'items' berisi item pertama
                    $mappedData[$codeSO] = array_merge($mappedRow, ['items' => [$mappedRow]]);
                }
            }

            // Mengubah array associatif menjadi array numerik jika diperlukan
            $result = array_values($mappedData);

            setSetting('import-so-' . $request->type . '-' . $user->id, count($result));
            $submitLog = OrderSubmitLog::create([
                'submited_by' => $user->id,
                'type_si' => 'import-so-' . $request->type,
                'vat' => 0,
                'tax' => 0,
                'ref_id' => null,
                'company_id' => $user->company_id
            ]);
            $file = $this->uploadFile($request, 'file', $submitLog->id);

            ImportSalesOrderUploadQueue::dispatch($submitLog->id, $request->type, $file)->onQueue('queue-backend');
            $no = 1;
            foreach ($result as $key => $item) {
                // Only pass necessary data
                ImportTransferKonsinyasiQueue::dispatch($item, $request->type, $no, $user->id, $submitLog->id, $file)->onQueue('queue-backend');

                $no++;
            }
            DB::commit();
            return response()->json([
                'data' => [],
                'message' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'data' => [],
                'message' => 'gagal import data',
                // 'error' => $th->getMessage()
            ], 400);
        }
    }

    public function uploadFile($request, $path = 'file', $reff_id)
    {
        if (!$request->hasFile($path)) {
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id
            ], [
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id,
                'status' => 'failed',
                'error_message' => 'File Tidak Ditemukan'
            ]);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id
            ], [
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id,
                'status' => 'failed',
                'error_message' => 'File Tidak Valid'
            ]);
        }
        // Get original filename and prepare path
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);

        // Create unique filename while preserving original name
        $uniqueFilename = $filename . '_' . time() . '.' . $extension;

        // Upload to S3 with original filename
        $uploaded = Storage::disk('s3')->putFileAs(
            $path,
            $file,
            $uniqueFilename,
            'public'
        );
        // $file = Storage::disk('s3')->put($path, $request[$path], 'public');
        return $uploaded;
    }
}
