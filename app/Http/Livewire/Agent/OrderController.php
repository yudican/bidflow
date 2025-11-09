<?php

namespace App\Http\Livewire\Agent;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Level;
use App\Models\Product;
use App\Models\Price;
use App\Models\ProductImage;
use App\Models\CommentRating;
use App\Models\LogApproveFinance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class OrderController extends Component
{
    use WithFileUploads;
    public $tbl_products_id;
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
    public $status = 1;
    public $image_path;
    public $code;
    public $commentlist = [];

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

    public $form_index = 1;

    protected $listeners = ['getDataProductById', 'getProductId', 'getCommentById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.agent.success', [
            'items' => Product::all(),
            'categories' => Category::where('status', 1)->get(),
            'brands' => Brand::all(),
        ]);
    }

    public function success()
    {
        return view('livewire.agent.success', [
            'items' => Product::all(),
            'categories' => Category::where('status', 1)->get(),
            'brands' => Brand::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $image = $this->image_path->store('upload', 'public');
            $data = [
                'category_id'  => $this->category_id,
                'brand_id'  => $this->brand_id,
                'name'  => $this->name,
                'slug'  => $this->slug,
                'code'  => $this->code,
                'description'  => $this->description,
                'image'  => $image,
                'stock'  => $this->stock,
                'weight'  => $this->weight,
                'is_varian'  => $this->is_varian,
                'status'  => 1
            ];
            $product = Product::create($data);

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
                $file = $image->store('upload', 'public');

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
                'category_id'  => $this->category_id,
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
                'stock'  => $this->stock,
                'weight'  => $this->weight,
                'is_varian'  => $this->is_varian,
                'status'  => $this->status
            ];
            $row = Product::find($this->tbl_products_id);

            if ($this->image_path) {
                $image = $this->image_path->store('upload', 'public');
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
                $file = $image->store('upload', 'public');

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
            LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_products_id, 'keterangan' => 'Update Product']);

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function delete()
    {
        Product::find($this->tbl_products_id)->delete();
        Price::where('product_id', $this->tbl_products_id)->delete();
        //log approval
        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_products_id, 'keterangan' => 'Delete Product']);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }


    public function getDataProductById($tbl_products_id)
    {
        $this->_reset();
        $row = Product::find($tbl_products_id);
        $this->tbl_products_id = $row->id;
        $this->category_id = $row->category_id;
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

    public function getCommentById($tbl_products_id)
    {
        $this->_reset();
        $comment = CommentRating::leftjoin('transaction_details', 'transaction_details.transaction_id', '=', 'comment_ratings.transaction_id')->leftjoin('users', 'comment_ratings.user_id', '=', 'users.id')->where('product_id', $tbl_products_id)->get();

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

    public function getProductId($tbl_products_id)
    {
        $row = Product::find($tbl_products_id);
        $this->tbl_products_id = $row->id;
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

    public function pilihPengiriman()
    {
        $this->emit('showModalPengiriman');
    }

    public function pilihPembayaran()
    {
        $this->emit('showModalPilihPembayaran');
    }

    public function addAlamat()
    {
        $this->emit('showModalAlamat');
    }

    public function addVoucher()
    {
        $this->emit('showModalVoucher');
    }

    public function addPayment()
    {
        $this->emit('showModalPayment');
    }
    
    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_products_id = null;
        $this->category_id = null;
        $this->brand_id = null;
        $this->name = null;
        $this->code = null;
        $this->slug = null;
        $this->description = null;
        $this->image_path = null;
        $this->stock = null;
        $this->weight = null;
        $this->is_varian = 0;
        $this->level_id = [];
        $this->basic_price = [];
        $this->final_price = [];
        $this->status = null;
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
        $this->image_lists = ProductImage::where('product_id', $this->tbl_products_id)->get();
    }
}
