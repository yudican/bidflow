<?php

namespace App\Http\Controllers\Spa\ProductManagement;

use App\Exports\ProductVariantExport;
use App\Jobs\CreateLogQueue;
use App\Exports\ProductVariantBaseInventoryExport;
use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\LogApproveFinance;
use App\Models\Price;
use App\Models\ProductCarton;
use App\Models\ProductImage;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundling;
use App\Models\ProductVariantBundlingStock;
use App\Models\ProductVariantStock;
use App\Models\Role;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Str;

class ProductVariantController extends Controller
{
    public function index($product_variant_id = null)
    {
        return view('spa.spa-index');
    }

    public function listProductVariant(Request $request)
    {
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

        if (isset($status)) {
            $product->where('status', $status);
        }

        if ($package_id) {
            $product->where('package_id', $package_id);
        }

        if ($variant_id) {
            $product->where('variant_id', $variant_id);
        }

        if ($sku) {
            $product->where('sku', $sku);
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

        $products = $product->orderBy('created_at', 'asc')->whereNull('deleted_at')->paginate($request->perpage);
        if ($role_id) {
            $role = Role::find($role_id);
            return response()->json([
                'status' => 'success',
                'data' => tap($products)->map(function ($item) use ($role, $wh_ids, $account_id) {
                    $item['final_price'] = $item->getPrice($role->role_type)['final_price'];
                    if ($item->is_bundling > 0) {
                        $warehouse = collect($item->stock_bundling)
                            ->whereIn('warehouse_id', $wh_ids);

                        if ($warehouse->isNotEmpty()) {
                            // Group by product_id and get minimum stock for each product
                            $minStocks = $warehouse->groupBy('product_id')
                                ->map(function ($items) use ($item) {
                                    $bundling = ProductVariantBundling::where('product_id', $item->product_id)->where('product_variant_id', $item->id)->first();
                                    return floor($items->sum('stock') / $bundling->product_qty);
                                })
                                ->min();

                            // Use the minimum stock as the total available stock
                            $item['stocks'] = $minStocks;
                            $item['stock_of_market'] = $minStocks;

                            // Override stock_warehouse with stock_bundling data for bundling products
                            $stock_warehouse = $warehouse->map(function ($bundling) use ($item) {
                                $bundlingData = ProductVariantBundling::where('product_id', $bundling['product_id'])->where('product_variant_id', $item->id)->first();
                                return [
                                    'warehouse_id' => $bundling['warehouse_id'],
                                    'warehouse_name' => $bundling['warehouse_name'],
                                    'product_id' => $bundling['product_id'],
                                    'stock' => floor($bundling['stock'] / $bundlingData->product_qty)
                                ];
                            });
                            $item['stock_warehouse'] = $stock_warehouse;

                            return $item;
                        }

                        $item['stocks'] = 0;
                        $item['stock_of_market'] = 0;
                        $item['stock_warehouse'] = collect();
                        return $item;
                    }

                    $stock_warehouse = ProductVariantStock::where('product_variant_id', $item->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id);
                    $item['stocks'] = $stock_warehouse->sum('qty') > 0 ? $stock_warehouse->sum('qty') : 0;
                    $item['stock_of_market'] = $stock_warehouse->sum('stock_of_market') > 0 ? $stock_warehouse->sum('stock_of_market') : 0;

                    return $item;
                }),
                'message' => 'List Product'
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => tap($products)->map(function ($product) use ($wh_ids, $account_id) {
                if ($product->is_bundling > 0) {
                    $warehouse = collect($product->stock_bundling)
                        ->whereIn('warehouse_id', $wh_ids);

                    if ($warehouse->isNotEmpty()) {
                        // Group by product_id and get minimum stock for each product
                        $minStocks = $warehouse->groupBy('product_id')
                            ->map(function ($items) use ($product) {
                                $bundling = ProductVariantBundling::where('product_id', $product->product_id)->where('product_variant_id', $product->id)->first();
                                return floor($items->sum('stock') / $bundling->product_qty);
                            })
                            ->min();

                        // Use the minimum stock as the total available stock
                        $product['stocks'] = $minStocks;
                        $product['stock_of_market'] = $minStocks;

                        // Override stock_warehouse with stock_bundling data for bundling products  
                        $stock_warehouse = $warehouse->map(function ($bundling) use ($product) {
                            $bundlingData = ProductVariantBundling::where('product_id', $bundling['product_id'])->where('product_variant_id', $product->id)->first();
                            return [
                                'warehouse_id' => $bundling['warehouse_id'],
                                'warehouse_name' => $bundling['warehouse_name'],
                                'product_id' => $bundling['product_id'],
                                'stock' => floor($bundling['stock'] / $bundlingData->product_qty)
                            ];
                        });
                        $product['stock_warehouse'] = $stock_warehouse;
                        $product['product_master_name'] = $product?->product?->name;

                        return $product;
                    }

                    $product['stocks'] = 0;
                    $product['stock_of_market'] = 0;
                    $product['stock_warehouse'] = collect();
                    $product['product_master_name'] = $product?->product?->name;
                    return $product;
                }

                $stock_warehouse = ProductVariantStock::where('product_variant_id', $product->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id);
                $product['stocks'] = $stock_warehouse->sum('qty') > 0 ? $stock_warehouse->sum('qty') : 0;
                $product['stock_of_market'] = $stock_warehouse->sum('stock_of_market') > 0 ? $stock_warehouse->sum('stock_of_market') : 0;
                $product['product_master_name'] = $product?->product?->name;
                return $product;
            }),
            'message' => 'List Product'
        ]);
    }

    public function getDetailProductVariant($product_variant_id = null)
    {
        if ($product_variant_id) {
            $product = ProductVariant::with(['product', 'bundlings', 'productImages'])->find($product_variant_id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'product' => $product,
                    'prices' => Level::all()->map(function ($item) use ($product) {
                        $price = $product->prices->where('level_id', $item->id)->first();
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'basic_price' => $price ? $price->basic_price : 0,
                            'final_price' => $price ? $price->final_price : 0,
                        ];
                    })
                ],
                'message' => 'Detail Product'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => null,
                'prices' => Level::all()->map(function ($item) {

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'basic_price' => 0,
                        'final_price' => 0,
                    ];
                })
            ],
            'message' => 'Detail Product'
        ]);
    }

    public function saveProductVariant(Request $request)
    {
        try {
            DB::beginTransaction();

            $image = Storage::disk('s3')->put('upload/product-variant', $request->image, 'public');
            $prices = json_decode($request->prices, true);
            $sales_channel = json_decode($request->sales_channel, true);
            $items = json_decode($request->items, true);
            $data = [
                'product_id'  => $request->product_id,
                'package_id'  => $request->package_id,
                'variant_id'  => $request->variant_id,
                'sku_variant'  => $request->sku_variant,
                'sku'  => $request->sku,
                'sku_marketplace'  => $request->sku_marketplace,
                'qty_bundling'  => $request->qty_bundling,
                'name'  => $request->name,
                'slug'  => Str::slug($request->slug),
                'description'  => $request->description,
                'sales_channel'  => implode(',', $sales_channel),
                'image'  => $image,
                'weight'  => $request->weight,
                'status'  => $request->status,
                'is_bundling'  => $request->is_bundling,
            ];
            $product = ProductVariant::create($data);
            foreach ($prices as $key => $value) {
                Price::create([
                    'product_variant_id' => $product->id,
                    'level_id' => $value['id'],
                    'basic_price' => $value['basic_price'],
                    'final_price' => $value['final_price'],
                ]);
            }

            if ($request->is_bundling > 0) {
                $newItems = json_decode($request->items, true);
                foreach ($newItems as $key => $item) {
                    ProductVariantBundling::create([
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $product->id,
                        'sku' => $item['sku'],
                        'product_qty' => $item['qty_variant'],
                        'product_qty' => $item['qty_variant'],
                        'package_id' => $item['uom'],
                        'company_id' => $request->account_id,
                    ]);
                }
            }

            $images  = [];
            foreach ($request->images as $image) {
                $file = Storage::disk('s3')->put('upload/product', $image, 'public');
                $images[] = [
                    'product_id' => $product->id,
                    'name' => $file,
                    'status' => 1,
                    'type' => 'product-variant',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            ProductImage::insert($images);

            $dataLog = [
                'log_type' => '[fis-dev]product_variant',
                'log_description' => 'Create Product Variant - ' . $product->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Produk Varian berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Produk Varian gagal disimpan',
                'errors' => $th->getMessage()
            ], 400);
        }
    }

    public function updateProductVariant(Request $request, $product_variant_id)
    {
        try {
            DB::beginTransaction();
            $product = ProductVariant::find($product_variant_id);
            $prices = json_decode($request->prices, true);
            $sales_channel = json_decode($request->sales_channel, true);
            $items = json_decode($request->items, true);
            $data = [
                'product_id'  => $request->product_id,
                'package_id'  => $request->package_id,
                'variant_id'  => $request->variant_id,
                'sku_variant'  => $request->sku_variant,
                'sku'  => $request->sku,
                'sku_marketplace'  => $request->sku_marketplace,
                'qty_bundling'  => $request->qty_bundling,
                'name'  => $request->name,
                'slug'  => Str::slug($request->slug),
                'description'  => $request->description,
                'sales_channel'  => implode(',', $sales_channel),
                'weight'  => $request->weight,
                'status'  => $request->status,
                'is_bundling'  => $request->is_bundling,
            ];

            if ($request->image) {
                $image = Storage::disk('s3')->put('upload/product-variant', $request->image, 'public');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $request->image)) {
                    Storage::delete('public/' . $request->image);
                }
            }

            $product->update($data);

            foreach ($prices as $key => $value) {
                $price = Price::where('product_variant_id', $product_variant_id)->where('level_id', $value['id'])->first();
                if ($price) {
                    $price->update([
                        'product_variant_id' => $product->id,
                        'level_id' => $value['id'],
                        'basic_price' => $value['basic_price'],
                        'final_price' => $value['final_price'],
                    ]);
                } else {
                    $price = Price::create([
                        'product_variant_id' => $product->id,
                        'level_id' => $value['id'],
                        'basic_price' => $value['basic_price'],
                        'final_price' => $value['final_price'],
                    ]);
                }
            }


            if ($request->is_bundling > 0) {
                $newItems = json_decode($request->items, true);
                ProductVariantBundling::where('product_variant_id', $product->id)->delete();
                foreach ($newItems as $key => $item) {
                    ProductVariantBundling::create([
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $product->id,
                        'sku' => $item['sku'],
                        'product_qty' => $item['qty_variant'],
                        'package_id' => $item['package_id'],
                        'is_master' =>  $key == 0 ? 1 : 0
                    ]);
                }
            }

            if (isset($request->images) && is_array($request->images)) {
                $images  = [];
                foreach ($request->images as $image) {
                    $file = Storage::disk('s3')->put('upload/product', $image, 'public');
                    $images[] = [
                        'product_id' => $product->id,
                        'name' => $file,
                        'status' => 1,
                        'type' => 'product-variant',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                ProductImage::insert($images);
            }

            $dataLog = [
                'log_type' => '[fis-dev]product_variant',
                'log_description' => 'Update Product Variant - ' . $product->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Produk Varian berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Produk Varian gagal disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateStatusProductVariant(Request $request, $product_variant_id)
    {
        $product = ProductVariant::find($product_variant_id);
        $product->status = $request->status;
        $product->save();

        $dataLog = [
            'log_type' => '[fis-dev]product_variant',
            'log_description' => 'Update Status Product Variant - ' . $product->id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Update Status Product'
        ]);
    }

    public function deleteProductVariant($product_variant_id)
    {
        $product = ProductVariant::find($product_variant_id);
        $product->update(['deleted_at' => Carbon::now()]);

        $dataLog = [
            'log_type' => '[fis-dev]product_variant',
            'log_description' => 'Delete Product Variant - ' . $product->id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Product Success'
        ]);
    }

    public function deleteProductBundling($product_bundling_id)
    {
        $product = ProductVariantBundling::find($product_bundling_id);
        if ($product->is_master == 1) {
            $newMaster = ProductVariantBundling::where('is_master', 0)->orderBy('id', 'ASC')->first();
            if ($newMaster) {
                $newMaster->productVariant()->update([
                    'package_id'  => $newMaster->package_id,
                    'sku'  => $newMaster->sku,
                    'qty_bundling'  => $newMaster->product_qty,
                    'product_id'  => $newMaster->product_id,
                ]);
                $newMaster->update(['is_master' => 1]);
            }
        }
        $product->delete();

        $dataLog = [
            'log_type' => '[fis-dev]product_variant',
            'log_description' => 'Delete Product Variant Bundling- ' . $product->id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Product Success'
        ]);
    }

    public function export(Request $request)
    {
        $file_name = 'product-variant.xlsx';
        Excel::store(new ProductVariantExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function exportBaseInventory(Request $request)
    {
        $file_name = 'product-variant.xlsx';
        Excel::store(new ProductVariantExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function productWithSku()
    {
        try {
            // Menggunakan chunk untuk mengurangi penggunaan memori
            $result = [];
            $query = ProductCarton::query()
                ->select(
                    'product_cartons.id',
                    'product_cartons.product_name',
                    'product_cartons.sku',
                    'product_cartons.moq',
                    'packages.name as uom'
                )
                ->join('packages', 'packages.id', '=', 'product_cartons.moq')
                ->orderBy('product_cartons.id');

            // Menggunakan cursor untuk streaming data
            foreach ($query->cursor() as $cartoon) {
                $result[] = [
                    'id' => $cartoon->id,
                    'name' => $cartoon->product_name,
                    'sku' => $cartoon->sku,
                    'uom' => $cartoon->uom,
                    'moq' => $cartoon->uom
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
                'message' => 'List Product Retrieved Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
