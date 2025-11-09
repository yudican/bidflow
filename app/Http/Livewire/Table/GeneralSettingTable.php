<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\GeneralSetting;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class GeneralSettingTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $table_name = 'tbl_general_settings';
    public $hide = [];

    public function builder()
    {
        return GeneralSetting::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('setting_code')->label('Setting Key')->searchable(),
            Column::name('setting_value')->label('Setting Value')->searchable(),

            Column::callback(['id'], function ($id) {
                return '<button class="btn btn-success btn-sm mr-2" wire:click="getDataById(' . $id . ')" id="btn-edit-' . $id . '"><i
                class="fas fa-edit"></i></button>';
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataGeneralSettingById', $id);
    }

    public function getId($id)
    {
        $this->emit('getGeneralSettingId', $id);
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
