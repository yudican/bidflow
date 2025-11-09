<?php

namespace App\Http\Livewire;

use App\Models\Level;
use App\Models\Price;
use App\Models\Role;
use Livewire\Component;


class LevelController extends Component
{

    public $tbl_levels_id;
    public $name;
    public $description;
    public $status;
    public $role_id = [];



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataLevelById', 'getLevelId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-levels', [
            'roles' => Role::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();
        $data = [
            'name'  => $this->name,
            'description'  => $this->description,
            'status'  => $this->status
        ];

        $level = Level::create($data);
        $level->roles()->attach($this->role_id);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'description'  => $this->description,
            'status'  => $this->status
        ];
        $row = Level::find($this->tbl_levels_id);



        $row->update($data);
        $row->roles()->sync($this->role_id);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Level::find($this->tbl_levels_id)->delete();
        Price::where('level_id', $this->tbl_levels_id)->delete();
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'description'  => 'required',
            'status'  => 'required',
            'role_id'  => 'required',
        ];



        return $this->validate($rule);
    }

    public function getDataLevelById($tbl_levels_id)
    {
        $this->_reset();
        $row = Level::find($tbl_levels_id);
        $this->tbl_levels_id = $row->id;
        $this->name = $row->name;
        $this->description = $row->description;
        $this->status = $row->status;
        $this->role_id = $row->roles->pluck('id')->toArray();
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getLevelId($tbl_levels_id)
    {
        $row = Level::find($tbl_levels_id);
        $this->tbl_levels_id = $row->id;
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
        $this->tbl_levels_id = null;
        $this->name = null;
        $this->description = null;
        $this->status = null;
        $this->role_id = [];
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
