<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\User;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\Brand;

class UserTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    // public $hideable = 'select';
    public $table_name = 'tbl_users';
    public $hide = [];

    public function builder()
    {
        $user = auth()->user();
        if ($user->role->role_type == 'agent') {
            return User::query()->whereHas('downlines', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $pengelola = User::query()->whereHas('roles', function ($query) {
            $query->where('role_type', '!=', 'member');
            $query->where('role_type', '!=', 'agent');
        })->orderBy('users.created_at', 'desc');

        return $pengelola;
    }

    public function columns()
    {
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Name')->searchable(),
            Column::name('email')->label('Email')->searchable(),
            Column::name('telepon')->label('Telepon')->searchable(),
            Column::callback('brand_id', function ($brand_id) {
                if ($brand_id) {
                    return Brand::find($brand_id)->name;
                }
                return '-';
            })->label('Brand'),
            Column::callback(['tbl_users.id', 'tbl_users.username'], function ($user_id, $username) {
                if ($user_id) {
                    return User::find($user_id)->role->role_type;
                }
                return '-';
            })->label('Role'),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataUserById', $id);
    }

    public function getId($id)
    {
        $this->emit('getUserId', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }
}
