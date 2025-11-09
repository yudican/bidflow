<?php

namespace App\Http\Livewire\UserManagement;

use App\Models\Role as ModelsRole;
use App\Models\Team;
use Livewire\Component;

class Role extends Component
{
    public $roles_id;
    public $role_type;
    public $role_name;


    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    public function render()
    {

        return view('livewire.usermanagement.roles', [
            'items' => ModelsRole::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        ModelsRole::create([
            'role_type'  => $this->role_type,
            'role_name'  => $this->role_name
        ]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();
        ModelsRole::find($this->roles_id)->update([
            'role_type'  => $this->role_type,
            'role_name'  => $this->role_name
        ]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ModelsRole::find($this->roles_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'role_type'  => 'required',
            'role_name'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataById($roles_id)
    {
        $roles = ModelsRole::find($roles_id);
        $this->roles_id = $roles->id;
        $this->role_type = $roles->role_type;
        $this->role_name = $roles->role_name;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getId($roles_id)
    {
        $roles = ModelsRole::find($roles_id);
        $this->roles_id = $roles->id;
    }

    public function toggleForm($form)
    {
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->roles_id = null;
        $this->role_type = null;
        $this->role_name = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
