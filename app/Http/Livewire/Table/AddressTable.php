<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Address;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class AddressTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_address_users';
    public $hide = [];

    public function builder()
    {
        return Address::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('type')->label('Type')->searchable(),
            Column::name('nama')->label('Nama')->searchable(),
            Column::name('alamat')->label('Alamat')->searchable(),
            Column::name('provinsi_id')->label('Provinsi Id')->searchable(),
            Column::name('kabupaten_id')->label('Kabupaten Id')->searchable(),
            Column::name('kecamatan_id')->label('Kecamatan Id')->searchable(),
            Column::name('kelurahan_id')->label('Kelurahan Id')->searchable(),
            Column::name('kodepos')->label('Kodepos')->searchable(),
            Column::name('telepon')->label('Telepon')->searchable(),
            Column::name('catatan')->label('Catatan')->searchable(),
            Column::name('user_id')->label('User Id')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataAddressById', $id);
    }

    public function getId($id)
    {
        $this->emit('getAddressId', $id);
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
