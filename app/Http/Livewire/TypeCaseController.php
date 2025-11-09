<?php

namespace App\Http\Livewire;

use App\Models\TypeCase;
use Livewire\Component;


class TypeCaseController extends Component
{
    
    public $tbl_type_cases_id;
    public $type_name;
    public $code;
    public $notes;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataTypeCaseById', 'getTypeCaseId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-type-cases', [
            'items' => TypeCase::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['type_name'  => $this->type_name,
                'code'  => $this->code,
                'notes'  => $this->notes];

        TypeCase::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['type_name'  => $this->type_name,
                'code'  => $this->code,
                'notes'  => $this->notes];
        $row = TypeCase::find($this->tbl_type_cases_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        TypeCase::find($this->tbl_type_cases_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'type_name'  => 'required',
            'code'  => 'required',
            // 'notes'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataTypeCaseById($tbl_type_cases_id)
    {
        $this->_reset();
        $row = TypeCase::find($tbl_type_cases_id);
        $this->tbl_type_cases_id = $row->id;
        $this->type_name = $row->type_name;
        $this->code = $row->code;
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

    public function getTypeCaseId($tbl_type_cases_id)
    {
        $row = TypeCase::find($tbl_type_cases_id);
        $this->tbl_type_cases_id = $row->id;
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
        $this->tbl_type_cases_id = null;
        $this->type_name = null;
        $this->code = null;
        $this->notes = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
