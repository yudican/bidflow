<?php

namespace App\Http\Livewire\UserManagement;

use App\Models\Permission as ModelsPermission;
use Livewire\Component;

class Permission extends Component
{
    public $permissions_id;
    public $permission_value;
    public $permission_name;


    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    public function render()
    {
        return view('livewire.usermanagement.permissions', [
            'items' => ModelsPermission::orderBy('created_at', 'ASC')->get()
        ]);
    }

    public function store()
    {
        $this->_validate();
        ModelsPermission::create([
            'permission_value'  => $this->permission_value,
            'permission_name'  => $this->permission_name
        ]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();
        ModelsPermission::find($this->permissions_id)->update([
            'permission_value'  => $this->permission_value,
            'permission_name'  => $this->permission_name
        ]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ModelsPermission::find($this->permissions_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'permission_value'  => 'required',
            'permission_name'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataById($permissions_id)
    {
        $permissions = ModelsPermission::find($permissions_id);
        $this->permissions_id = $permissions->id;
        $this->permission_value = $permissions->permission_value;
        $this->permission_name = $permissions->permission_name;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getId($permissions_id)
    {
        $permissions = ModelsPermission::find($permissions_id);
        $this->permissions_id = $permissions->id;
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
        $this->permissions_id = null;
        $this->permission_value = null;
        $this->permission_name = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
