<?php

namespace App\Http\Livewire;

use App\Models\PriorityCase;
use Livewire\Component;


class PriorityCaseController extends Component
{
    
    public $tbl_priority_cases_id;
    public $priority_name;
public $priority_day;
public $notes;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataPriorityCaseById', 'getPriorityCaseId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-priority-cases', [
            'items' => PriorityCase::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['priority_name'  => $this->priority_name,
'priority_day'  => $this->priority_day,
'notes'  => $this->notes];

        PriorityCase::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['priority_name'  => $this->priority_name,
'priority_day'  => $this->priority_day,
'notes'  => $this->notes];
        $row = PriorityCase::find($this->tbl_priority_cases_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        PriorityCase::find($this->tbl_priority_cases_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'priority_name'  => 'required',
'priority_day'  => 'required',
'notes'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataPriorityCaseById($tbl_priority_cases_id)
    {
        $this->_reset();
        $row = PriorityCase::find($tbl_priority_cases_id);
        $this->tbl_priority_cases_id = $row->id;
        $this->priority_name = $row->priority_name;
$this->priority_day = $row->priority_day;
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

    public function getPriorityCaseId($tbl_priority_cases_id)
    {
        $row = PriorityCase::find($tbl_priority_cases_id);
        $this->tbl_priority_cases_id = $row->id;
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
        $this->tbl_priority_cases_id = null;
        $this->priority_name = null;
$this->priority_day = null;
$this->notes = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
