<?php

namespace App\Http\Livewire;

use App\Models\StatusCase;
use Livewire\Component;


class StatusCaseController extends Component
{
    
    public $tbl_status_cases_id;
    public $status_name;
public $notes;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataStatusCaseById', 'getStatusCaseId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-status-cases', [
            'items' => StatusCase::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['status_name'  => $this->status_name,
'notes'  => $this->notes];

        StatusCase::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['status_name'  => $this->status_name,
'notes'  => $this->notes];
        $row = StatusCase::find($this->tbl_status_cases_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        StatusCase::find($this->tbl_status_cases_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'status_name'  => 'required',
'notes'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataStatusCaseById($tbl_status_cases_id)
    {
        $this->_reset();
        $row = StatusCase::find($tbl_status_cases_id);
        $this->tbl_status_cases_id = $row->id;
        $this->status_name = $row->status_name;
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

    public function getStatusCaseId($tbl_status_cases_id)
    {
        $row = StatusCase::find($tbl_status_cases_id);
        $this->tbl_status_cases_id = $row->id;
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
        $this->tbl_status_cases_id = null;
        $this->status_name = null;
$this->notes = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
