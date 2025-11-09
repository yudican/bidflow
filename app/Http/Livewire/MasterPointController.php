<?php

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\MasterPoint;
use Livewire\Component;


class MasterPointController extends Component
{

    public $tbl_master_points_id;
    public $type;
    public $point;
    public $min_trans;
    public $max_trans;
    public $brand_id = [];
    // public $percentage;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataMasterPointById', 'getMasterPointId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-master-points', [
            'brands' => Brand::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'point'  => $this->point,
            'min_trans'  => $this->min_trans,
            'max_trans'  => $this->max_trans,
            'type'  => $this->type
        ];
        $point = MasterPoint::updateOrCreate(['type'  => $this->type], $data);
        $point->brands()->attach($this->brand_id);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'point'  => $this->point,
            'min_trans'  => $this->min_trans,
            'max_trans'  => $this->max_trans,
            'type'  => $this->type
        ];
        $row = MasterPoint::find($this->tbl_master_points_id);

        $row->update($data);
        $row->brands()->sync($this->brand_id);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        MasterPoint::find($this->tbl_master_points_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'point'  => 'required|numeric',
            'brand_id' => 'required'
        ];

        if ($this->type == 'transaction') {
            $rule['min_trans'] = 'required|numeric';
            $rule['max_trans'] = 'required|numeric';
        }

        return $this->validate($rule);
    }

    public function getDataMasterPointById($tbl_master_points_id)
    {
        $this->_reset();
        $row = MasterPoint::find($tbl_master_points_id);
        $this->tbl_master_points_id = $row->id;
        $this->point = $row->point;
        $this->min_trans = $row->min_trans;
        $this->max_trans = $row->max_trans;
        $this->brand_id = $row->brands()->pluck('brands.id')->toArray();
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

    public function getMasterPointId($tbl_master_points_id)
    {
        $row = MasterPoint::find($tbl_master_points_id);
        $this->tbl_master_points_id = $row->id;
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
        $this->tbl_master_points_id = null;
        $this->point = null;
        $this->min_trans = null;
        $this->max_trans = null;
        $this->brand_id = [];
        $this->type = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
