<?php

namespace App\Http\Livewire\UserManagement;

use App\Models\Brand;
use App\Models\Downline;
use App\Models\Role;
use App\Models\Team;
use App\Models\User as ModelsUser;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class User extends Component
{
    public $users_id;
    public $role_id;
    public $team_id = 1;
    public $name;
    public $email;
    public $brand_id;
    public $password;

    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataUserById', 'getUserId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $user = auth()->user();
        $roles = Role::all();
        if ($user->role->role_type == 'agent') {
            $roles = Role::whereIn('role_type', ['subagent'])->get();
        }

        return view('livewire.usermanagement.users', [
            'roles' => $roles,
            'brands' => Brand::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        if (is_array($this->brand_id)) {
            if (count($this->brand_id) == 0) {
                return $this->addError('brand_id', 'Minimal harus pilih 1 brand');
            }
        }
        $checkmail = ModelsUser::where('email', $this->email)->first();
        if (!empty($checkmail)) {
            return $this->emit('showAlertError', ['msg' => 'Email telah digunakan']);
        }

        $role = Role::find($this->role_id);
        $user = ModelsUser::create([
            'name'  => $this->name,
            'email'  => $this->email,
            'brand_id'  => $this->brand_id[0],
            'password'  => Hash::make($this->password)
            // 'password'  => Hash::make($role_type . '123')
        ]);
        $user->brands()->attach($this->brand_id);
        $user->roles()->attach($role->id);
        $user->teams()->attach(1, ['role' => $role->role_type]);

        if (auth()->user()->role->role_type == 'agent') {
            Downline::create(['user_id' => auth()->user()->id, 'member_id' => $user->id]);
        }

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();
        if (is_array($this->brand_id)) {
            if (count($this->brand_id) == 0) {
                return $this->addError('brand_id', 'Minimal harus pilih 1 brand');
            }
        }
        $user = ModelsUser::find($this->users_id);
        $role = Role::find($this->role_id);
        $data = [
            'name'  => $this->name,
            'email'  => $this->email,
            'brand_id'  => $this->brand_id[0],
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);
        $user->brands()->sync($this->brand_id);
        $user->roles()->sync($role->id);
        $user->teams()->sync(1, ['role' => $role->role_type]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ModelsUser::find($this->users_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'role_id'  => 'required',
            'brand_id'  => 'required',
        ];

        return $this->validate($rule);
    }

    public function getDataUserById($users_id)
    {
        $users = ModelsUser::find($users_id);
        $this->users_id = $users->id;
        $this->name = $users->name;
        $this->email = $users->email;
        $this->role_id = $users->role->id;
        $this->brand_id = $users->brand_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getUserId($users_id)
    {
        $users = ModelsUser::find($users_id);
        $this->users_id = $users->id;
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
        $this->emit('refreshTable');
        $this->clearValidation();
        $this->users_id = null;
        $this->role_id = null;
        $this->brand_id = null;
        $this->name = null;
        $this->email = null;
        $this->password = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
