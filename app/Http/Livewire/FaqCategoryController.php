<?php

namespace App\Http\Livewire;

use App\Models\FaqCategory;
use Livewire\Component;


class FaqCategoryController extends Component
{
    
    public $tbl_faq_categories_id;
    public $category;
public $status;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataFaqCategoryById', 'getFaqCategoryId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-faq-categories', [
            'items' => FaqCategory::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['category'  => $this->category,
'status'  => $this->status];

        FaqCategory::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['category'  => $this->category,
'status'  => $this->status];
        $row = FaqCategory::find($this->tbl_faq_categories_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        FaqCategory::find($this->tbl_faq_categories_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'category'  => 'required',
'status'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataFaqCategoryById($tbl_faq_categories_id)
    {
        $this->_reset();
        $row = FaqCategory::find($tbl_faq_categories_id);
        $this->tbl_faq_categories_id = $row->id;
        $this->category = $row->category;
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

    public function getFaqCategoryId($tbl_faq_categories_id)
    {
        $row = FaqCategory::find($tbl_faq_categories_id);
        $this->tbl_faq_categories_id = $row->id;
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
        $this->tbl_faq_categories_id = null;
        $this->category = null;
$this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
