<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Banner;
use App\Models\Brand;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class BannerTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_banners';
    // public $hide = [];

    public function builder()
    {
        return Banner::query();
    }

    public function columns()
    {
        // $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('title')->label('Judul')->searchable()->width(10),
            Column::name('image')->label(__('Image')),
            Column::name('slug')->label('Slug')->hide(),
            Column::name('description')->label('Description')->hide(),
            Column::callback(['tbl_banners.brand_id', 'tbl_banners.id'], function ($brand_id, $id) {
                $row = Banner::find($id);
                if ($row) {
                    return $row->brands->pluck('name')->implode(', ');
                }
                return '-';
            })->label('Brand'),
            Column::callback(['banners.status'], function ($status) {
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

    public function getDataById($id)
    {
        $this->emit('getDataBannerById', $id);
    }

    public function getId($id)
    {
        $this->emit('getBannerId', $id);
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
