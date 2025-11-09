<?php

namespace App\Http\Livewire;

use App\Models\MasterDiscount;
use Livewire\Component;


class MasterDiscountController extends Component
{
    
    public $tbl_master_discounts_id;
    public $title;
public $percentage;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataMasterDiscountById', 'getMasterDiscountId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-master-discounts', [
            'items' => MasterDiscount::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['title'  => $this->title,
'percentage'  => $this->percentage];

        MasterDiscount::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['title'  => $this->title,
'percentage'  => $this->percentage];
        $row = MasterDiscount::find($this->tbl_master_discounts_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        MasterDiscount::find($this->tbl_master_discounts_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'title'  => 'required',
'percentage'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataMasterDiscountById($tbl_master_discounts_id)
    {
        $this->_reset();
        $row = MasterDiscount::find($tbl_master_discounts_id);
        $this->tbl_master_discounts_id = $row->id;
        $this->title = $row->title;
$this->percentage = $row->percentage;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getMasterDiscountId($tbl_master_discounts_id)
    {
        $row = MasterDiscount::find($tbl_master_discounts_id);
        $this->tbl_master_discounts_id = $row->id;
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
        $this->tbl_master_discounts_id = null;
        $this->title = null;
$this->percentage = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
