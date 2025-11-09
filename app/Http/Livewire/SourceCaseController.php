<?php

namespace App\Http\Livewire;

use App\Models\SourceCase;
use Livewire\Component;


class SourceCaseController extends Component
{
    
    public $tbl_source_cases_id;
    public $source_name;
    public $notes;
    public $type;
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataSourceCaseById', 'getSourceCaseId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-source-cases', [
            'items' => SourceCase::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['source_name'  => $this->source_name,
                'notes'  => $this->notes,
                'type'  => $this->type
            ];

        SourceCase::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['source_name'  => $this->source_name,
                'notes'  => $this->notes,
                'type'  => $this->type
            ];
        $row = SourceCase::find($this->tbl_source_cases_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        SourceCase::find($this->tbl_source_cases_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'source_name'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataSourceCaseById($tbl_source_cases_id)
    {
        $this->_reset();
        $row = SourceCase::find($tbl_source_cases_id);
        $this->tbl_source_cases_id = $row->id;
        $this->source_name = $row->source_name;
        $this->notes = $row->notes;
        $this->type = $row->type;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getSourceCaseId($tbl_source_cases_id)
    {
        $row = SourceCase::find($tbl_source_cases_id);
        $this->tbl_source_cases_id = $row->id;
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
        $this->tbl_source_cases_id = null;
        $this->source_name = null;
$this->notes = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
