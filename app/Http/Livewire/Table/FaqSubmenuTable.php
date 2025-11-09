<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\FaqSubmenu;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class FaqSubmenuTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_faq_sub_menus';
    public $hide = [];

    public function builder()
    {
        return FaqSubmenu::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('sub_menu')->label('Sub Menu')->searchable(),
            Column::callback(['faq_sub_menus.is_like'], function ($is_like) {
                if ($is_like == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Is Like'),
            Column::callback(['faq_sub_menus.is_comment'], function ($is_comment) {
                if ($is_comment == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Is Comment'),
            Column::callback(['faq_sub_menus.status'], function ($status) {
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
        $this->emit('getDataFaqSubmenuById', $id);
    }

    public function getId($id)
    {
        $this->emit('getFaqSubmenuId', $id);
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
