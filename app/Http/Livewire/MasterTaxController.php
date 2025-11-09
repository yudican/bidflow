<?php

namespace App\Http\Livewire;

use App\Models\MasterTax;
use Livewire\Component;


class MasterTaxController extends Component
{
    
    public $tbl_master_tax_id;
    public $tax_code;
public $tax_percentage;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataMasterTaxById', 'getMasterTaxId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-master-tax', [
            'items' => MasterTax::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['tax_code'  => $this->tax_code,
'tax_percentage'  => $this->tax_percentage];

        MasterTax::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['tax_code'  => $this->tax_code,
'tax_percentage'  => $this->tax_percentage];
        $row = MasterTax::find($this->tbl_master_tax_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        MasterTax::find($this->tbl_master_tax_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'tax_code'  => 'required',
'tax_percentage'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataMasterTaxById($tbl_master_tax_id)
    {
        $this->_reset();
        $row = MasterTax::find($tbl_master_tax_id);
        $this->tbl_master_tax_id = $row->id;
        $this->tax_code = $row->tax_code;
$this->tax_percentage = $row->tax_percentage;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getMasterTaxId($tbl_master_tax_id)
    {
        $row = MasterTax::find($tbl_master_tax_id);
        $this->tbl_master_tax_id = $row->id;
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
        $this->tbl_master_tax_id = null;
        $this->tax_code = null;
$this->tax_percentage = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
