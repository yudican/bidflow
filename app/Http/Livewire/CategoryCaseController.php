<?php

namespace App\Http\Livewire;

use App\Models\CategoryCase;
use App\Models\TypeCase;
use Livewire\Component;


class CategoryCaseController extends Component
{
    
    public $tbl_category_cases_id;
    public $type_id;
    public $category_name;
    public $notes;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataCategoryCaseById', 'getCategoryCaseId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-category-cases', [
            'items' => CategoryCase::all(),
            'types' => TypeCase::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['type_id'  => $this->type_id,
                'category_name'  => $this->category_name,
                'notes'  => $this->notes];

        CategoryCase::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['type_id'  => $this->type_id,
                'category_name'  => $this->category_name,
                'notes'  => $this->notes];
        $row = CategoryCase::find($this->tbl_category_cases_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        CategoryCase::find($this->tbl_category_cases_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'type_id'  => 'required',
            'category_name'  => 'required',
            // 'notes'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataCategoryCaseById($tbl_category_cases_id)
    {
        $this->_reset();
        $row = CategoryCase::find($tbl_category_cases_id);
        $this->tbl_category_cases_id = $row->id;
        $this->type_id = $row->type_id;
        $this->category_name = $row->category_name;
        $this->notes = $row->notes;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getCategoryCaseId($tbl_category_cases_id)
    {
        $row = CategoryCase::find($tbl_category_cases_id);
        $this->tbl_category_cases_id = $row->id;
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
        $this->tbl_category_cases_id = null;
        $this->type_id = null;
        $this->category_name = null;
        $this->notes = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
