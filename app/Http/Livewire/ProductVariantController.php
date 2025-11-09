<?php

namespace App\Http\Livewire;

use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductImage;
use App\Models\Warehouse;
use App\Models\Package;
use App\Models\Variant;
use App\Models\Level;
use App\Models\Price;
use App\Models\SkuMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class ProductVariantController extends Component
{
    use WithFileUploads;
    public $tbl_product_variants_id;
    public $product_id;
    public $package_id;
    public $variant_id;
    public $name;
    public $slug;
    public $description;
    public $image;
    public $agent_price;
    public $customer_price;
    public $discount_price;
    public $discount_percent;
    public $stock;
    public $weight;
    public $status = 1;
    public $sku;
    public $sku_variant;
    public $image_path;
    public $warehouse_id;
    public $stock2;
    public $product_variant_id;
    public $qty_bundling;

    // multiple image
    public $images;
    public $image_lists;
    public $images_path;

    // dinamic form images
    public $inputs = [0, 1, 2, 3, 4, 5];
    public $i;

    public $level_id = [];
    public $basic_price = [];
    public $final_price = [];
    public $warehouses = [];
    public $product_variants = [];

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $stock_list = false;

    public $items = [];
    public $products = [];
    public $packages = [];
    public $variants = [];
    public $levels = [];
    public $skumasters = [];
    public $productvariants = [];

    protected $listeners = ['getDataProductVariantById', 'getProductVariantId', 'getStockById'];
    public function mount()
    {
        $this->route_name = request()->route()->getName();
        $this->items = ProductVariant::all();
        $this->products = Product::all();
        $this->packages = Package::all();
        $this->variants = Variant::all();
        $this->levels = Level::all();
        $this->skumasters = SkuMaster::all();
        $this->warehouses = Warehouse::all();
        $this->productvariants = ProductVariant::all();
    }

    public function render()
    {
        $this->slug = Str::slug($this->name, '-');
        return view('livewire.tbl-product-variants');
    }

    public function store()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $image = Storage::disk('s3')->put('upload/product', $this->image_path, 'public');
            $data = [
                'product_id'  => $this->product_id,
                'package_id'  => $this->package_id,
                'variant_id'  => $this->variant_id,
                'name'  => $this->name,
                'slug'  => $this->slug,
                'description'  => $this->description,
                'image'  => $image,
                'stock'  => $this->stock,
                'weight'  => $this->weight,
                'sku'  => $this->sku,
                'sku_variant' => $this->sku_variant,
                'status'  => $this->status,
                'qty_bundling'  => $this->qty_bundling,
            ];

            $product = ProductVariant::create($data);

            // Input Prices
            $prices = [];
            foreach ($this->basic_price as $key => $value) {
                $prices[] = [
                    'level_id' => $key,
                    'basic_price' => $value,
                    'final_price' => $this->final_price[$key],
                    'product_id' => $this->product_id,
                    'product_variant_id' => $product->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            Price::insert($prices);

            $images  = [];
            foreach ($this->images as $image) {
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

            $this->_reset();
            DB::commit();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function update()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $data = [
                'product_id'  => $this->product_id,
                'package_id'  => $this->package_id,
                'variant_id'  => $this->variant_id,
                'slug'  => $this->slug,
                'description'  => $this->description,
                'stock'  => $this->stock,
                'weight'  => $this->weight,
                'sku'  => $this->sku,
                'sku_variant'  => $this->sku_variant,
                'status'  => $this->status,
                'qty_bundling'  => $this->qty_bundling,
            ];
            $row = ProductVariant::find($this->tbl_product_variants_id);

            if ($this->image_path) {
                // $image = $this->image_path->store('upload', 'public');
                $image = Storage::disk('s3')->put('upload/product', $this->image, 'public');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $this->image)) {
                    Storage::delete('public/' . $this->image);
                }
            }

            $row->update($data);

            // Update Prices
            $row->prices()->delete();
            $prices = [];
            foreach ($this->basic_price as $key => $value) {
                $prices[] = [
                    'level_id' => $key,
                    'basic_price' => $value,
                    'final_price' => $this->final_price[$key],
                    'product_id' => $this->product_id,
                    'product_variant_id' => $row->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            Price::insert($prices);

            $this->_reset();
            DB::commit();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function delete()
    {
        $variant = ProductVariant::find($this->tbl_product_variants_id);
        $variant->prices()->delete();
        $variant->delete();
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function store_stock()
    {
        $varian = ProductVariant::find($this->product_id);
        $data = [
            'product_id'  => $varian->product_id,
            'product_variant_id'  => $this->product_id,
            'warehouse_id'  => $this->warehouse_id,
            'stock'  => $this->stock2
        ];
        $check = ProductStock::where('product_variant_id', $this->product_id)->where('warehouse_id', $this->warehouse_id)->first();
        if (empty($check)) {
            $stock = ProductStock::create($data);
            $varian->update(['stock' => $this->stock2]);
        } else {
            $data['stock'] = $check->stock + $this->stock2;
            $check->update($data);
            $varian->update($data);
        }

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function _validate()
    {
        $rule = [
            'product_id'  => 'required',
            'package_id'  => 'required',
            'variant_id'  => 'required',
            'name'  => 'required',
            'weight'  => 'required',
            'qty_bundling' => 'required|numeric'
        ];

        if (!$this->update_mode) {
            $rule['image_path'] = 'required';
        }

        return $this->validate($rule);
    }

    public function getDataProductVariantById($tbl_product_variants_id)
    {
        $this->_reset();
        $row = ProductVariant::find($tbl_product_variants_id);
        $this->tbl_product_variants_id = $row->id;
        $this->product_id = $row->product_id;
        $this->package_id = $row->package_id;
        $this->variant_id = $row->variant_id;
        $this->name = $row->name;
        $this->description = $row->description;
        $this->image = $row->image;
        $this->stock = $row->stock;
        $this->weight = $row->weight;
        $this->sku = $row->sku;
        $this->sku_variant = $row->sku_variant;
        $this->status = $row->status;
        $this->qty_bundling = $row->qty_bundling;

        // get prices
        $this->basic_price = $row->prices()->pluck('basic_price', 'level_id')->toArray();
        $this->final_price = $row->prices()->pluck('final_price', 'level_id')->toArray();
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm', auth()->user()->id);
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getProductVariantId($tbl_product_variants_id)
    {
        $row = ProductVariant::find($tbl_product_variants_id);
        $this->tbl_product_variants_id = $row->id;
    }

    public function getStockById($product_id)
    {
        $this->_reset();
        $stock = ProductStock::where('product_variant_id', $product_id)->get();

        if ($this->form) {
            $this->form_active = false;
            $this->stock_list = true;
            $this->stocklist = $stock;
            $this->product_id = $product_id;
            $this->warehouses = Warehouse::all();
            $this->emit('loadForm', auth()->user()->id);
        }
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm', auth()->user()->id);
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function showModalStock($product_id)
    {
        $this->_reset();
        $stock = ProductStock::where('product_id', $product_id)->get();
        $variant = ProductVariant::find($product_id);
        $this->stocklist = $stock;
        $this->warehouses = Warehouse::all();
        $this->product_id = $product_id;
        $this->product_variants = ProductVariant::where('product_id', $variant->product_id)->get();
        $this->emit('showModalStock');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_product_variants_id = null;
        $this->product_id = null;
        $this->package_id = null;
        $this->variant_id = null;
        $this->name = null;
        $this->slug = null;
        $this->description = null;
        $this->image_path = null;
        $this->agent_price = null;
        $this->customer_price = null;
        $this->discount_price = null;
        $this->discount_percent = null;
        $this->stock = null;
        $this->sku = null;
        $this->qty_bundling = null;
        $this->sku_variant = null;
        $this->weight = null;
        $this->status = 1;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->images = [];
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs, $i);
    }

    public function unSelect($i)
    {
        unset($this->images[$i]);
    }
    public function deleteImage($image_id)
    {
        $image = ProductImage::find($image_id);
        $image->delete();
        $this->image_lists = ProductImage::where('product_id', $this->product_id)->get();
    }
}
