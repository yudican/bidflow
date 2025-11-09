<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\LeadActivity;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class LeadActivityTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_lead_activities';
    public $hide = [];

    public function builder()
    {
        return LeadActivity::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('uid_lead')->label('Uid Lead')->searchable(),
            Column::name('title')->label('Title')->searchable(),
            Column::name('description')->label('Description')->searchable(),
            Column::name('start_date')->label('Start Date')->searchable(),
            Column::name('end_date')->label('End Date')->searchable(),
            Column::name('result')->label('Result')->searchable(),
            Column::callback(['attachment'], function ($file) {
                return '<a href="{{asset("storage/" . $file)}}">show file</a>';
            })->label(__('Attachment')),
            Column::name('status')->label('Status')->searchable(),
            Column::name('user_created')->label('User Created')->searchable(),
            Column::name('user_updated')->label('User Updated')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataLeadActivityById', $id);
    }

    public function getId($id)
    {
        $this->emit('getLeadActivityId', $id);
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
