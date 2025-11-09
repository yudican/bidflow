<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Brand;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class BrandTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_brands';
    public $hide = [];

    public function builder()
    {
        return Brand::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Nama')->searchable(),
            Column::name('code')->label('Kode')->searchable(),
            Column::name('slug')->label('Slug')->searchable(),
            Column::callback(['logo'], function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => getImage($image),
                ]);
            })->label(__('Logo')),
            Column::name('phone')->label('Phone')->searchable(),
            Column::name('email')->label('Email')->searchable(),
            Column::name('address')->label('Address')->searchable(),
            Column::name('twitter')->label('Twitter')->searchable(),
            Column::name('facebook')->label('Facebook')->searchable(),
            Column::name('instagram')->label('Instagram')->searchable(),
            Column::callback('status', function ($status) {
                if ($status == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Status'),
            // Column::name('description')->label('Description')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataBrandById', $id);
    }

    public function getId($id)
    {
        $this->emit('getBrandId', $id);
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
