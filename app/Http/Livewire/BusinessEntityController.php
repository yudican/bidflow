<?php

namespace App\Http\Livewire;

use App\Models\BusinessEntity;
use Livewire\Component;


class BusinessEntityController extends Component
{
    
    public $tbl_business_entities_id;
    public $title;
public $description;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataBusinessEntityById', 'getBusinessEntityId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-business-entities', [
            'items' => BusinessEntity::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['title'  => $this->title,
'description'  => $this->description];

        BusinessEntity::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['title'  => $this->title,
'description'  => $this->description];
        $row = BusinessEntity::find($this->tbl_business_entities_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        BusinessEntity::find($this->tbl_business_entities_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'title'  => 'required',
'description'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataBusinessEntityById($tbl_business_entities_id)
    {
        $this->_reset();
        $row = BusinessEntity::find($tbl_business_entities_id);
        $this->tbl_business_entities_id = $row->id;
        $this->title = $row->title;
$this->description = $row->description;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getBusinessEntityId($tbl_business_entities_id)
    {
        $row = BusinessEntity::find($tbl_business_entities_id);
        $this->tbl_business_entities_id = $row->id;
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
        $this->tbl_business_entities_id = null;
        $this->title = null;
$this->description = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
