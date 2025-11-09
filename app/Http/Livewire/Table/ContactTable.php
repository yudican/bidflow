<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\User;
use App\Models\Level;
use App\Models\Role;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Cache;
#use Illuminate\Support\Facades\Redis;

class ContactTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable', 'applyFilter'];
    public $hideable = 'select';
    public $table_name = 'tbl_users';
    public $hide = [];
    public $filters = [];
    public $params = 'contact';

    public function builder()
    {
        $ip_address = getIp();
        $expire = Carbon::now()->addMinutes(10);
        $user = Cache::remember('auth_user_' . $ip_address, $expire, function () {
            return auth()->user();
        });
        if (in_array($user->role->role_type, ['superadmin', 'adminsales', 'leadwh', 'leadsales'])) {
            $contact = User::query()->whereHas('roles', function ($query) {
                $query->where('role_type', '!=', 'superadmin');
            })->orderBy('users.created_at', 'desc');

            if (isset($this->filters['role_id']) && isset($this->filters['status'])) {
                if ($this->filters['role_id'] != 'all') {
                    $contact->whereHas('roles', function ($query) {
                        $query->where('role_id', $this->filters['role_id']);
                    });
                }
                if ($this->filters['status'] != 'all') {
                    $contact->where('status', $this->filters['status']);
                }
            }

            return $contact;
        } else {
            $contact = User::query()->where('created_by', $user->id)->orderBy('users.created_at', 'desc');
            if (isset($this->filters['role_id']) && isset($this->filters['status'])) {
                if ($this->filters['role_id'] != 'all') {
                    $contact->whereHas('roles', function ($query) {
                        $query->where('role_id', $this->filters['role_id']);
                    });
                }
                if ($this->filters['status'] != 'all') {
                    $contact->where('status', $this->filters['status']);
                }
            }

            return $contact;
        }
        return  User::query()->orderBy('users.created_at', 'desc');
    }

    public function columns()
    {
        // $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.')->width('5%'),
            Column::name('name')->label('Name')->searchable(),
            Column::name('email')->label('Email')->searchable(),
            Column::callback(['tbl_users.id', 'tbl_users.username'], function ($user_id, $username) {
                $expire = Carbon::now()->addDays(1);
                $user = Cache::remember('user_' . $user_id, $expire, function () use ($user_id) {
                    return User::find($user_id);
                });
                if ($user) {
                    return $user->roles()->pluck('role_name')->implode(', ');
                }
                return '-';
            })->label('Role'),
            Column::callback(['created_at'], function ($created_at) {
                return date_format(new DateTime($created_at), 'd F Y, H:i');
            })->label('Created On'),
            Column::callback('created_by', function ($created_by) {
                $expire = Carbon::now()->addDays(1);
                $user = Cache::remember('user_' . $created_by, $expire, function () use ($created_by) {
                    return User::find($created_by);
                });
                return $user ? $user->name : '-';
            })->label('Created By'),
            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action Contact')),
        ];
    }

    public function getId($id)
    {
        $this->emit('getUserDataId', $id);
    }

    public function getDetailById($id)
    {
        $this->emit('getDetailById', $id);
    }

    public function getDetailById2($id)
    {
        $this->emit('getDetailById2', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }



    public function applyFilter($filters = [])
    {
        $this->filters = $filters;
    }
}
