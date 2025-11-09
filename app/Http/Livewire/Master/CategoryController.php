<?php

namespace App\Http\Livewire\Master;

use App\Models\Category;
use Livewire\Component;


class CategoryController extends Component
{

    public $tbl_categories_id;
    public $nama_kategori;



    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataCategoryById', 'getCategoryId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.tbl-categories', [
            'items' => Category::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = ['nama_kategori'  => $this->nama_kategori];

        Category::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['nama_kategori'  => $this->nama_kategori];
        $row = Category::find($this->tbl_categories_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Category::find($this->tbl_categories_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'nama_kategori'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataCategoryById($tbl_categories_id)
    {
        $this->_reset();
        $row = Category::find($tbl_categories_id);
        $this->tbl_categories_id = $row->id;
        $this->nama_kategori = $row->nama_kategori;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getCategoryId($tbl_categories_id)
    {
        $row = Category::find($tbl_categories_id);
        $this->tbl_categories_id = $row->id;
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
        $this->tbl_categories_id = null;
        $this->nama_kategori = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
