<?php

namespace App\Http\Controllers\Spa\ProductManagement;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Exports\ProductMasterExport;
use App\Models\LogApproveFinance;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\ProductVariantBundlingStock;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Str;

class ProductMasterController extends Controller
{
    public function index($product_id = null)
    {
        return view('spa.spa-index');
    }

    public function listProductMaster(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $warehouse_id = $request->warehouse_ids;
        $account_id = $request->account_id;
        $wh_ids = [];

        $product =  Product::query();
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status && is_array($status)) {
            $status = array_map(function ($item) {
                return $item == 10 ? 0 : $item;
            }, $status);
    
            $product->whereIn('status', $status);
        }

        if (is_array($warehouse_id)) {
            $wh_ids = $warehouse_id;
            if (in_array('all', $warehouse_id)) {
                $wh_ids = Warehouse::pluck('id')->toArray();
            }
        }

        $products = $product->orderBy('created_at', 'asc')->whereNull('deleted_at')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => tap($products)->map(function ($product) use ($wh_ids, $account_id) {
                $stock_warehouse = ProductStock::where('product_id', $product->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id)->sum('stock');
                $stock_bundling_warehouse = ProductVariantBundlingStock::whereHas('bundling', function ($query) use ($wh_ids, $product) {
                    $query->where('product_id', $product->id);
                })->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id)->orderBy('qty', 'asc')->first(['qty']);
                $product->stock_by_warehouse = $stock_warehouse > 0 ? $stock_warehouse : 0;
                $product->stock_bundling_warehouse = $stock_bundling_warehouse ? $stock_bundling_warehouse->qty : 0;

                return $product;
            }),
            'message' => 'List Product'
        ]);
    }


    public function getDetailProductMaster($product_id)
    {
        $product = Product::with('productImages')->find($product_id);

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Detail Product'
        ]);
    }

    public function updateStatusProductMaster(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        $product->status = $request->status;
        $product->save();

        $dataLog = [
            'log_type' => '[fis-dev]product_master',
            'log_description' => 'Update Product Master - ' . $product_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');


        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Update Status Product'
        ]);
    }

    public function saveProductMaster(Request $request)
    {
        try {
            DB::beginTransaction();


            $categories = json_decode($request->category_id, true);
            $data = [
                'category_id'  => $categories[0],
                'brand_id'  => $request->brand_id,
                'name'  => $request->name,
                'slug'  => Str::slug($request->slug),
                'code'  => $request->code,
                'sku'  => $request->sku,
                'stock'  => $request->stock ?? 0,
                'description'  => $request->description,
                'weight'  => $request->weight,
                'is_varian'  => 1,
                'product_like'  => $request->product_like ?? 0,
                'status'  => $request->status,
                'product_carton_id'  => $request->product_carton_id,
            ];

            if ($request->image) {
                $image = Storage::disk('s3')->put('upload/product', $request->image, 'public');
                $data['image'] = $image;
            }

            $product = Product::create($data);
            $product->categories()->attach($categories);

            $images  = [];
            foreach ($request->images as $image) {
                $file = Storage::disk('s3')->put('upload/product', $image, 'public');
                $images[] = [
                    'product_id' => $product->id,
                    'name' => $file,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            ProductImage::insert($images);

            $dataLog = [
                'log_type' => '[fis-dev]product_master',
                'log_description' => 'Create Product Master - ' . $product->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            // Trigger orca
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->send('POST', 'https://brcd-testing.flimty.co/api/fis/trigger/products', [
                'body' => '{}', // Kirim JSON kosong sebagai string
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Produk berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Produk gagal disimpan'
            ], 400);
        }
    }


    public function updateProductMaster(Request $request, $product_id)
    {
        try {
            DB::beginTransaction();
            $product = Product::find($product_id);
            $categories = json_decode($request->category_id, true);
            $data = [
                'category_id'  => $categories[0],
                'brand_id'  => $request->brand_id,
                'name'  => $request->name,
                'slug'  => Str::slug($request->slug),
                'code'  => $request->code,
                'sku'  => $request->sku,
                'stock'  => $request->stock ?? 0,
                'description'  => $request->description,
                'weight'  => $request->weight,
                'is_varian'  => 1,
                'product_like'  => $request->product_like ?? 0,
                'status'  => $request->status,
                'product_carton_id'  => $request->product_carton_id,
            ];

            if ($request->image) {
                $image = $this->uploadImage($request, 'image');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $request->image)) {
                    Storage::delete('public/' . $request->image);
                }
            }

            $product->categories()->sync($categories);
            $product->update($data);

            if ($request->images) {
                $images  = [];
                foreach ($request->images as $key => $image) {
                    $file = Storage::disk('s3')->put('upload/product', $image, 'public');

                    $images = [
                        'product_id' => $product->id,
                        'name' => $file,
                        'status' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                ProductImage::insert($images);
            }

            $dataLog = [
                'log_type' => '[fis-dev]product_master',
                'log_description' => 'Update Product Master - ' . $product->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Save Product Success'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Product Failed to Save',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function deleteProductMaster($product_id)
    {
        $product = Product::find($product_id);
        $product->update(['deleted_at' => Carbon::now()]);
        // $product->variants()->update(['deleted_at' => Carbon::now()]);
        //log approval
        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $product_id, 'keterangan' => 'Delete Product']);

        $dataLog = [
            'log_type' => '[fis-dev]product_master',
            'log_description' => 'Delete Product Master - ' . $product->id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Product Success'
        ]);
    }

    public function handleDeleteProductImages($product_images_id)
    {
        $product_images = ProductImage::find($product_images_id);
        $product_images->delete();

        $dataLog = [
            'log_type' => '[fis-dev]product_master',
            'log_description' => 'Delete Image Product Master - ' . $product_images_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Product Images Success'
        ]);
    }

    public function updateStockProduct(Request $request, $product_id)
    {
        foreach ($request->data as $key => $product) {
            $variant = ProductVariant::find($product['id']);
            $variant->increment('stock', $product['stock']);
        }
        $dataLog = [
            'log_type' => '[fis-dev]product_master',
            'log_description' => 'Update Stock Product Master - ' . $product_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Update Stock Product'
        ]);
    }

    public function export(Request $request)
    {
        $file_name = 'convert/FIS-Product_Master-' . date('d-m-Y') . '.xlsx';

        Excel::store(new ProductMasterExport($request), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }
        $file = Storage::disk('s3')->put('upload/user', $request[$path], 'public');
        return $file;
    }
}
