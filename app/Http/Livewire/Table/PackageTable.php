<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Package;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class PackageTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_packages';
    public $hide = [];

    public function builder()
    {
        return Package::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Nama Package')->searchable(),
            Column::name('slug')->label('Slug')->searchable(),
            // Column::name('description')->label('Deskripsi'),
            Column::callback(['packages.id'], function ($id) {
                $trans = Package::find($id);
                $user = auth()->user();

                return '<div>
                    <button class="btn btn-warning btn-sm mr-2" wire:click="viewDetail(' . $id . ')" id="btn-edit-' . $id . '"><i class="fas fa-eye"></i> Lihat Detail</button></div>';
            })->label(__('Deskripsi')),
            Column::callback(['packages.status'], function ($status) {
                if ($status == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Status'),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function viewDetail($id)
    {
        $this->emit('viewDetail', $id);
    }

    public function getDataById($id)
    {
        $this->emit('getDataPackageById', $id);
    }

    public function getId($id)
    {
        $this->emit('getPackageId', $id);
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
