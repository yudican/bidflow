<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\SettingDescription;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class SettingDescriptionTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_setting_descriptions';
    public $hide = [];

    public function builder()
    {
        return SettingDescription::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('general_info_point')->label('General Info Point')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataSettingDescriptionById', $id);
    }

    public function getId($id)
    {
        $this->emit('getSettingDescriptionId', $id);
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
