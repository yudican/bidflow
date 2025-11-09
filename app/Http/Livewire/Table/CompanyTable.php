<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Company;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class CompanyTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_companies';
    public $hide = [];

    public function builder()
    {
        return Company::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Name')->searchable(),
            Column::name('address')->label('Address')->searchable(),
            Column::name('email')->label('Email')->searchable(),
            Column::name('phone')->label('Phone')->searchable(),
            Column::name('brand_id')->label('Brand Id')->searchable(),
            Column::name('owner_name')->label('Owner Name')->searchable(),
            Column::name('owner_phone')->label('Owner Phone')->searchable(),
            Column::name('pic_name')->label('Pic Name')->searchable(),
            Column::name('pic_phone')->label('Pic Phone')->searchable(),
            Column::name('user_id')->label('User Id')->searchable(),
            Column::name('status')->label('Status')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataCompanyById', $id);
    }

    public function getId($id)
    {
        $this->emit('getCompanyId', $id);
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
