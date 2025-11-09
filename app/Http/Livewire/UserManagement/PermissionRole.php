<?php

namespace App\Http\Livewire\UserManagement;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class PermissionRole extends Component
{
  public $role_id, $permission_id = [];

  public function mount($role_id)
  {
    $this->role_id = $role_id;
    $permission_id = Permission::with('roles')->whereHas('roles', function ($query) use ($role_id) {
      return $query->where('roles.id', $role_id);
    })->orderBy('created_at', 'ASC')->pluck('permissions.id')->toArray();
    $this->permission_id = $permission_id;
  }
  public function render()
  {
    // dd($this->permission_id);
    // dd(Permission::with('roles')->get());
    return view('livewire.usermanagement.permission-role', [
      'items' => Permission::with('roles')->orderBy('created_at', 'ASC')->get()
    ]);
  }

  public function store()
  {
    Role::find($this->role_id)->permissions()->sync($this->permission_id);

    return redirect('role');
  }
}
