<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\NotifAlert;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use Maatwebsite\Excel\Facades\Excel;

class NotifTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_notif_alert';
    public $hide = [];

    public $exportable = true;

    public function builder()
    {
        return NotifAlert::query()->orderBy('id', 'ASC');
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Notif Alert')->searchable(),
            Column::callback(['notif_alert.status'], function ($status) {
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
        $this->emit('getDataNotifById', $id);
    }

    public function getId($id)
    {
        $this->emit('getNotifId', $id);
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
