<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\AgentDetail;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class AgentDetailTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_agent_details';
    public $hide = [];

    public function builder()
    {
        return AgentDetail::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('user.name')->label('Nama')->searchable(),
            Column::name('user.telepon')->label('Telepon')->searchable(),
            Column::callback([$this->table_name . '.active', $this->table_name . '.id'], function ($status, $id) {
                return view('livewire.components.toggle-status', [
                    'id' => $id,
                    'active' => $status,
                    'field' => 'active',
                ]);
            })->label(__('Status')),
            Column::callback([$this->table_name . '.libur', $this->table_name . '.id'], function ($status, $id) {
                return view('livewire.components.toggle-status', [
                    'id' => $id,
                    'active' => $status,
                    'field' => 'libur',
                ]);
            })->label(__('Libur')),
        ];
    }

    public function toggleStatus($id, $field)
    {
        $detail = AgentDetail::find($id);
        if ($detail) {
            $detail->update([
                $field => $detail[$field] == 0 ? 1 : 0
            ]);
        }
    }

    public function getDataById($id)
    {
        $this->emit('getDataAgentDetailById', $id);
    }

    public function getId($id)
    {
        $this->emit('getAgentDetailId', $id);
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
