<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\UserData;
use App\Models\Level;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class UserDataTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_user_datas';
    public $hide = [];

    public function builder()
    {
        return UserData::query()->whereHas('user', function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('role_type', 'member');
            });
        });
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::callback(['user_datas.level_id'], function ($level_id) {
                $level = Level::find($level_id);
                if ($level) {
                    return $level->name;
                }
                return '-';
            })->label('Level'),
            Column::name('class_id')->label('Class Id'),
            Column::name('customer_id')->label('Customer Id')->searchable(),
            Column::name('short_name')->label('Short Name')->searchable(),
            Column::name('address')->label('Address'),
            Column::name('contact_person')->label('Contact Person'),
            Column::name('city')->label('City'),
            Column::name('state')->label('State'),
            Column::name('country')->label('Country'),
            Column::name('zip_code')->label('Zip Code'),
            Column::name('phone1')->label('Phone1'),
            Column::name('phone2')->label('Phone2'),
            Column::name('phone3')->label('Phone3'),
            Column::name('fax')->label('Fax'),
            Column::name('npwp')->label('Npwp'),
            Column::name('status')->label('Status'),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataUserDataById', $id);
    }

    public function getId($id)
    {
        $this->emit('getUserDataId', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }

    public function toggle($index)
    {
        if ($this->sort == $index) {
            $this->initialiseSort();
        }

        $column = HideableColumn::where([
            'table_name' => $this->table_name,
            'column_name' => $this->columns[$index]['name'],
            'index' => $index,
            'user_id' => auth()->user()->id
        ])->first();

        if (!$this->columns[$index]['hidden']) {
            unset($this->activeSelectFilters[$index]);
        }

        $this->columns[$index]['hidden'] = !$this->columns[$index]['hidden'];

        if (!$column) {
            HideableColumn::updateOrCreate([
                'table_name' => $this->table_name,
                'column_name' => $this->columns[$index]['name'],
                'index' => $index,
                'user_id' => auth()->user()->id
            ]);
        } else {
            $column->delete();
        }
    }
}
