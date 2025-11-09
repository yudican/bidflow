<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Level;
use App\Models\Product;
use App\Models\Price;
use App\Models\Warehouse;
use App\Models\ProductImage;
use App\Models\CommentRating;
use App\Models\ProductStock;
use App\Models\SkuMaster;
use App\Models\LogApproveFinance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class ProductController extends Component
{
    use WithFileUploads;
    public $product_id;
    public $category_id;
    public $brand_id;
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
    public $is_varian = 0;
    public $status = 0;
    public $product_like = 0;
    public $image_path;
    public $code;
    public $commentlist = [];
    public $warehouses = [];
    public $warehouse_id;
    public $stock2;

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

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $comment = false;
    public $stock_list = false;

    public $form_index = 1;

    protected $listeners = ['getDataProductById', 'getProductId', 'getCommentById', 'getStockById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $levels = Level::all();

        $this->slug = Str::slug($this->name, '-');
        return view('livewire.tbl-products', [
            'items' => Product::all(),
            'categories' => Category::where('status', 1)->get(),
            'brands' => Brand::all(),
            'levels' => $levels,
            'warehouses' => Warehouse::all(),
            'skumasters' => SkuMaster::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            // $image = $this->image_path->store('upload', 'public');
            $image = Storage::disk('s3')->put('upload/product', $this->image_path, 'public');
            $data = [
                'category_id'  => $this->category_id[0],
                'brand_id'  => $this->brand_id,
                'name'  => $this->name,
                'slug'  => $this->slug,
                'code'  => $this->code,
                'description'  => $this->description,
                'image'  => $image,
                // 'stock'  => $this->stock,
                'weight'  => $this->weight,
                'is_varian'  => $this->is_varian,
                'product_like'  => $this->product_like ?? 0,
                'status'  => 1,
            ];
            $product = Product::create($data);
            $product->categories()->attach($this->category_id);
            // Input Prices
            $prices = [];
            foreach ($this->basic_price as $key => $value) {
                $prices[] = [
                    'level_id' => $key,
                    'basic_price' => $value,
                    'final_price' => $this->final_price[$key],
                    'product_id' => $product->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            $images  = [];
            foreach ($this->images as $image) {
                // $file = $image->store('upload', 'public');
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
            Price::insert($prices);

            DB::commit();
            $this->_reset();
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
                'category_id'  => $this->category_id[0],
                'brand_id'  => $this->brand_id,
                'name'  => $this->name,
                'slug'  => $this->slug,
                'code'  => $this->code,
                'description'  => $this->description,
                'image'  => $this->image,
                'agent_price'  => $this->agent_price,
                'customer_price'  => $this->customer_price,
                'discount_price'  => $this->discount_price,
                'discount_percent'  => $this->discount_percent,
                // 'stock'  => $this->stock,
                'weight'  => $this->weight,
                'is_varian'  => $this->is_varian,
                'status'  => $this->status,
                'product_like'  => $this->product_like,
            ];
            $row = Product::find($this->product_id);
            $row->categories()->sync($this->category_id);
            if ($this->image_path) {
                // $image = $this->image_path->store('upload', 'public');
                $image = Storage::disk('s3')->put('upload/product', $this->image_path, 'public');
                $data = ['image' => $image];
                if (Storage::exists('public/' . $this->image)) {
                    Storage::delete('public/' . $this->image);
                }
            }

            $row->update($data);

            // Update Prices
            if ($row->prices->count() > 0) {
                foreach ($row->prices as $key => $price) {
                    $price->update([
                        'basic_price' => $this->basic_price[$price->level_id],
                        'final_price' => $this->final_price[$price->level_id],
                    ]);
                }
            } else {
                // jika price belum diinput
                $prices = [];
                foreach ($this->basic_price as $key => $value) {
                    $prices[] = [
                        'level_id' => $key,
                        'basic_price' => $value,
                        'final_price' => $this->final_price[$key],
                        'product_id' => $row->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                Price::insert($prices);
            }

            $images  = [];
            foreach ($this->images as $image) {
                // $file = $image->store('upload', 'public');
                $file = Storage::disk('s3')->put('upload/product', $image, 'public');
                $images[] = [
                    'product_id' => $row->id,
                    'name' => $file,
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            ProductImage::insert($images);

            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->product_id, 'keterangan' => 'Update Product']);

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function getProductId($product_id)
    {
        $this->product_id = $product_id;
    }
    public function delete()
    {
        try {
            DB::beginTransaction();
            $product = Product::find($this->product_id);
            $product->update(['deleted_at' => Carbon::now()]);
            //log approval
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->product_id, 'keterangan' => 'Delete Product']);

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Dihapus']);
        }
    }

    public function store_stock()
    {
        // $this->_validate();
        $data = [
            'product_id'  => $this->product_id,
            'warehouse_id'  => $this->warehouse_id,
            'stock'  => $this->stock2
        ];
        $check = ProductStock::where('product_id', $this->product_id)->where('warehouse_id', $this->warehouse_id)->first();
        if (empty($check)) {
            $stock = ProductStock::create($data);
        } else {
            $data['stock'] = $check->stock + $this->stock2;
            $check->update($data);
        }

        $this->emit('closeModal');
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function nextForm($form = 1, $validate = true)
    {
        if ($validate) {
            $this->_validate($form - 2);
            $this->form_index = $form;
        } else {
            $this->form_index = $form;
        }
    }

    public function _validate($form = 1)
    {
        $rule = [];
        switch ($form) {
            case 1:
                $rule = [
                    'category_id'  => 'required',
                    'brand_id'  => 'required',
                    'name'  => 'required',
                    // 'stock'  => 'required',
                    'weight'  => 'required',
                    // 'code'  => 'required'
                ];

                break;

            case 2:
                // validate price
                // $levels = Level::all();
                // foreach ($levels as $key => $level) {
                //     $rule['basic_price.' . $level->id] = 'required|numeric|min:0';
                //     $rule['final_price.' . $level->id] = 'required|numeric|min:0';
                // }
                // break;

            default:
                $rule = [
                    'category_id'  => 'required',
                    'brand_id'  => 'required',
                    'name'  => 'required',
                    // 'stock'  => 'required',
                    'weight'  => 'required',
                    // 'status'  => 'required'
                ];

                if (!$this->update_mode) {
                    $rule['image_path'] = 'required';
                    $rule['images'] = 'required';
                }

                // $levels = Level::all();
                // foreach ($levels as $key => $level) {
                //     $rule['basic_price.' . $level->id] = 'required|numeric|min:0';
                //     $rule['final_price.' . $level->id] = 'required|numeric|min:0';
                // }
        }

        return $this->validate($rule);
    }

    public function getDataProductById($product_id)
    {
        $this->_reset();
        $row = Product::find($product_id);
        $this->product_id = $row->id;
        $this->category_id = $row->categories()->pluck('categories.id')->toArray();
        $this->brand_id = $row->brand_id;
        $this->name = $row->name;
        $this->slug = $row->slug;
        $this->code = $row->code;
        $this->description = $row->description;
        $this->image = $row->image;
        $this->agent_price = $row->agent_price;
        $this->customer_price = $row->customer_price;
        $this->discount_price = $row->discount_price;
        $this->discount_percent = $row->discount_percent;
        $this->stock = $row->stock;
        $this->weight = $row->weight;
        $this->is_varian = $row->is_varian;
        $this->status = $row->status;
        $this->image_lists = $row->productImages;
        $this->product_like = $row->product_like;


        // get prices
        $this->basic_price = $row->prices()->pluck('basic_price', 'level_id')->toArray();
        $this->final_price = $row->prices()->pluck('final_price', 'level_id')->toArray();

        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getCommentById($product_id)
    {
        $this->_reset();
        $comment = CommentRating::leftjoin('transaction_details', 'transaction_details.transaction_id', '=', 'comment_ratings.transaction_id')->leftjoin('users', 'comment_ratings.user_id', '=', 'users.id')->where('product_id', $product_id)->get();

        if ($this->form) {
            $this->form_active = false;
            $this->comment = true;
            $this->commentlist = $comment;
            $this->emit('loadForm');
        }
        // if ($this->modal) {
        //     $this->emit('showModal');
        // }
        // $this->update_mode = true;
    }

    public function getStockById($product_id)
    {
        $this->_reset();
        $stock = ProductStock::where('product_id', $product_id)->get();

        if ($this->form) {
            $this->form_active = false;
            $this->stock_list = true;
            $this->stocklist = $stock;
            $this->product_id = $product_id;
            $this->warehouses = Warehouse::all();
            $this->emit('loadForm');
        }
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm');

        if (!$this->update_mode) {
            $levels = Level::all();
            foreach ($levels as $key => $level) {
                $this->basic_price[$level->id] = 0;
                $this->final_price[$level->id] = 0;
            }
        }
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
        $this->stocklist = $stock;
        $this->warehouses = Warehouse::all();
        $this->product_id = $product_id;
        $this->emit('showModalStock');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->product_id = null;
        $this->category_id = null;
        $this->brand_id = null;
        $this->name = null;
        $this->code = null;
        $this->slug = null;
        $this->description = null;
        $this->image = null;
        $this->image_path = null;
        $this->stock = null;
        $this->weight = null;
        $this->is_varian = 0;
        $this->level_id = [];
        $this->basic_price = [];
        $this->final_price = [];
        $this->status = null;
        $this->product_like = 0;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
        $this->comment = false;
        $this->form_index = 1;
        $this->images = [];
        $this->image_lists = [];
        $this->images_path = [''];
        $this->inputs = [0];
        $this->i = 0;
        $this->stock = null;
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
