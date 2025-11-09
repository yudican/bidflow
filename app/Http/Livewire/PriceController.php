<?php

namespace App\Http\Livewire;

use App\Models\Price;
use Livewire\Component;


class PriceController extends Component
{
    
    public $tbl_prices_id;
    public $level_id;
    public $product_id;
    public $product_variant_id;
    public $basic_price;
    public $final_price;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataPriceById', 'getPriceId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-prices', [
            'items' => Price::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['level_id'  => $this->level_id,
                'product_id'  => $this->product_id,
                'product_variant_id'  => $this->product_variant_id,
                'basic_price'  => $this->basic_price,
                'final_price'  => $this->final_price];

        Price::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['level_id'  => $this->level_id,
                'product_id'  => $this->product_id,
                'product_variant_id'  => $this->product_variant_id,
                'basic_price'  => $this->basic_price,
                'final_price'  => $this->final_price];
        $row = Price::find($this->tbl_prices_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Price::find($this->tbl_prices_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'level_id'  => 'required',
            'product_id'  => 'required',
            'product_variant_id'  => 'required',
            'basic_price'  => 'required',
            'final_price'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataPriceById($tbl_prices_id)
    {
        $this->_reset();
        $row = Price::find($tbl_prices_id);
        $this->tbl_prices_id = $row->id;
        $this->level_id = $row->level_id;
        $this->product_id = $row->product_id;
        $this->product_variant_id = $row->product_variant_id;
        $this->basic_price = $row->basic_price;
        $this->final_price = $row->final_price;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getPriceId($tbl_prices_id)
    {
        $row = Price::find($tbl_prices_id);
        $this->tbl_prices_id = $row->id;
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
        $this->tbl_prices_id = null;
        $this->level_id = null;
        $this->product_id = null;
        $this->product_variant_id = null;
        $this->basic_price = null;
        $this->final_price = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
