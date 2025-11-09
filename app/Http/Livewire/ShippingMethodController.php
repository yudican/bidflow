<?php

namespace App\Http\Livewire;

use App\Models\ShippingMethod;
use Livewire\Component;


class ShippingMethodController extends Component
{
    
    public $tbl_shipping_methods_id;
    public $code;
public $name;
public $status;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataShippingMethodById', 'getShippingMethodId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-shipping-methods', [
            'items' => ShippingMethod::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['code'  => $this->code,
'name'  => $this->name,
'status'  => $this->status];

        ShippingMethod::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['code'  => $this->code,
'name'  => $this->name,
'status'  => $this->status];
        $row = ShippingMethod::find($this->tbl_shipping_methods_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ShippingMethod::find($this->tbl_shipping_methods_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'code'  => 'required',
'name'  => 'required',
'status'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataShippingMethodById($tbl_shipping_methods_id)
    {
        $this->_reset();
        $row = ShippingMethod::find($tbl_shipping_methods_id);
        $this->tbl_shipping_methods_id = $row->id;
        $this->code = $row->code;
$this->name = $row->name;
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

    public function getShippingMethodId($tbl_shipping_methods_id)
    {
        $row = ShippingMethod::find($tbl_shipping_methods_id);
        $this->tbl_shipping_methods_id = $row->id;
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
        $this->tbl_shipping_methods_id = null;
        $this->code = null;
$this->name = null;
$this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
