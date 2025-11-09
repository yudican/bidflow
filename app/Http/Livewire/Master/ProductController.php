<?php

namespace App\Http\Livewire\Master;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductController extends Component
{
    use WithFileUploads;
    public $tbl_products_id;
    public $nama_product;
    public $harga_product;
    public $gambar_produk;
    public $category_id;
    public $gambar_produk_path;
    public $deskripsi;


    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataProductById', 'getProductId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.tbl-products', [
            'items' => Product::all(),
            'categories' => Category::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        // $gambar_produk = $this->gambar_produk_path->store('upload', 'public');
        $gambar_produk = Storage::disk('s3')->put('upload/product', $this->gambar_produk_path, 'public');
        $data = [
            'nama_product'  => $this->nama_product,
            'harga_product'  => $this->harga_product,
            'gambar_produk'  => $gambar_produk,
            'category_id'  => $this->category_id,
            'deskripsi'  => $this->deskripsi,
        ];

        Product::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'nama_product'  => $this->nama_product,
            'harga_product'  => $this->harga_product,
            'gambar_produk'  => $this->gambar_produk,
            'category_id'  => $this->category_id,
            'deskripsi'  => $this->deskripsi,
        ];
        $row = Product::find($this->tbl_products_id);


        if ($this->gambar_produk_path) {
            // $gambar_produk = $this->gambar_produk_path->store('upload', 'public');
            $gambar_produk = Storage::disk('s3')->put('upload/product', $this->gambar_produk_path, 'public');
            $data = ['gambar_produk' => $gambar_produk];
            if (Storage::exists('public/' . $this->gambar_produk)) {
                Storage::delete('public/' . $this->gambar_produk);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Product::find($this->tbl_products_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'nama_product'  => 'required',
            'harga_product'  => 'required',
            // 'gambar_produk_path'  => 'required',
            'category_id'  => 'required',
            'deskripsi'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataProductById($tbl_products_id)
    {
        $this->_reset();
        $row = Product::find($tbl_products_id);
        $this->tbl_products_id = $row->id;
        $this->nama_product = $row->nama_product;
        $this->harga_product = $row->harga_product;
        $this->gambar_produk = $row->gambar_produk;
        $this->category_id = $row->category_id;
        $this->deskripsi = $row->deskripsi;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
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
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_products_id = null;
        $this->nama_product = null;
        $this->harga_product = null;
        $this->gambar_produk_path = null;
        $this->category_id = null;
        $this->deskripsi = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
