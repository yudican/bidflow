<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\LogApproveFinance;
use Livewire\Component;
use Illuminate\Support\Str;

class CategoryController extends Component
{

    public $tbl_categories_id;
    public $name;
    public $slug;
    public $status;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataCategoryById', 'getCategoryId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->name, '-');
        return view('livewire.tbl-categories', [
            'items' => Category::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'status'  => $this->status
        ];

        Category::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'status'  => $this->status
        ];
        $row = Category::find($this->tbl_categories_id);
        $row->update($data);

        //log approval
        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_categories_id, 'keterangan' => 'Update Category']);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Category::find($this->tbl_categories_id)->delete();
        //log approval
        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_categories_id, 'keterangan' => 'Delete Category']);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'slug'  => 'required',
            'status'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataCategoryById($tbl_categories_id)
    {
        $this->_reset();
        $row = Category::find($tbl_categories_id);
        $this->tbl_categories_id = $row->id;
        $this->name = $row->name;
        $this->slug = $row->slug;
        $this->status = $row->status;
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
        $this->name = null;
        $this->slug = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
