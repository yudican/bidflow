<?php

namespace App\Http\Livewire;

use App\Models\Package;
use App\Models\SkuMaster;
use GuzzleHttp\Client;
use Livewire\Component;


class SkuMasterController extends Component
{

    public $tbl_sku_masters_id;
    public $sku;
    public $package_id;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataSkuMasterById', 'getSkuMasterId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-sku-masters', [
            'items' => SkuMaster::all(),
            'packages' => Package::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = ['sku'  => $this->sku, 'package_id' => $this->package_id];

        SkuMaster::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();


        $data = ['sku'  => $this->sku, 'package_id' => $this->package_id];
        $row = SkuMaster::find($this->tbl_sku_masters_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        SkuMaster::find($this->tbl_sku_masters_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'sku'  => 'required',
            'package_id'  => 'required',
        ];



        return $this->validate($rule);
    }

    public function getDataSkuMasterById($tbl_sku_masters_id)
    {
        $this->_reset();
        $row = SkuMaster::find($tbl_sku_masters_id);
        $this->tbl_sku_masters_id = $row->id;
        $this->sku = $row->sku;
        $this->package_id = $row->package_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getSkuMasterId($tbl_sku_masters_id)
    {
        $row = SkuMaster::find($tbl_sku_masters_id);
        $this->tbl_sku_masters_id = $row->id;
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
        $this->tbl_sku_masters_id = null;
        $this->sku = null;
        $this->package_id = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
