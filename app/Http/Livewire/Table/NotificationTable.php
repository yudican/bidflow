<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Notification;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class NotificationTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_notifications';
    public $hide = [];

    public function builder()
    {
        $user = auth()->user();
        return Notification::query()->where('role_id', $user->role->id);
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('title')->label('Title')->searchable(),
            Column::name('body')->label('Body')->searchable(),
            Column::name('created_at')->label('Created on'),
            Column::callback(['id'], function ($id) {
                $action = '<button class="btn btn-success btn-sm mr-2" wire:click="getDataById(' . $id . ')" id="btn-edit-' . $id . '"><i class="fas fa-eye"></i></button>';
                return $action;
                // return view('livewire.components.action-button', [
                //     'id' => $id,
                //     'segment' => $this->params
                // ]);
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataNotificationById', $id);
    }

    public function getId($id)
    {
        $this->emit('getNotificationId', $id);
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
