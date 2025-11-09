<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Menu;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class MenuTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'menus';
    public $hide = [];


    public function builder()
    {
        return Menu::query()->orderBy('menu_order', 'ASC');
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('menu_label')->label('Menu Name')->searchable(),
            Column::name('menu_route')->label('Menu Route')->searchable(),
            Column::callback(['menu_icon'], function ($menu_icon) {
                return '<i class="' . $menu_icon . '"></i>';
            })->label('Menu Icon')->searchable(),
            Column::name('menu_order')->label('Menu Order')->searchable(),
            Column::callback(['parent_id'], function ($parent_id) {
                $menu = Menu::find($parent_id);
                if ($menu) {
                    return Menu::find($parent_id)->menu_label;
                }
                return '-';
            })->label('Parent Menu')->searchable(),

            Column::callback(['id'], function ($id) {
                return view('livewire.components.action-button', [
                    'id' => $id,
                    'segment' => request()->segment(1)
                ]);
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataById', $id);
    }

    public function getId($id)
    {
        $this->emit('getId', $id);
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
