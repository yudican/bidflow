<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Cases;
use App\Models\User;
use App\Models\TypeCase;
use App\Models\CategoryCase;
use App\Models\StatusCase;
use App\Models\PriorityCase;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class CaseTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable', 'applyFilter'];
    // public $hideable = 'select';
    public $table_name = 'tbl_case_masters';
    public $hide = [];
    public $filters = [];

    public function builder()
    {
        if (isset($this->filters['type']) && isset($this->filters['priority']) && isset($this->filters['status'])) {
            return Cases::query()->where('type_id', $this->filters['type'])->where('priority_id', $this->filters['priority'])->where('status_id', $this->filters['status'])->orderBy('created_at', 'DESC');
        }
        if (isset($this->filters['type'])) {
            if ($this->filters['type'] == 'all') {
                return Cases::query()->orderBy('created_at', 'DESC');
            }
            return Cases::query()->where('type_id', $this->filters['type'])->orderBy('created_at', 'DESC');
        }
        if (isset($this->filters['priority'])) {
            if ($this->filters['priority'] == 'all') {
                return Cases::query();
            }
            return Cases::query()->where('priority_id', $this->filters['priority'])->orderBy('created_at', 'DESC');
        }
        if (isset($this->filters['status'])) {
            if ($this->filters['status'] == 'all') {
                return Cases::query()->orderBy('created_at', 'DESC');
            }
            return Cases::query()->where('status_id', $this->filters['status'])->orderBy('created_at', 'DESC');
        }



        return Cases::query()->orderBy('created_at', 'DESC');
    }

    public function columns()
    {
        // $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('title')->label('Case No.')->searchable(),
            Column::callback('contact', function ($contact) {
                $row = User::find($contact);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Contact'),
            Column::callback('type_id', function ($type_id) {
                $row = TypeCase::find($type_id);
                if ($row) {
                    return $row->type_name;
                }
                return '-';
            })->label('Type'),
            Column::callback('category_id', function ($category_id) {
                $row = CategoryCase::find($category_id);
                if ($row) {
                    return $row->category_name;
                }
                return '-';
            })->label('Category'),
            Column::callback('priority_id', function ($priority_id) {
                $row = PriorityCase::find($priority_id);
                if ($row) {
                    return $row->priority_name;
                }
                return '-';
            })->label('Priority'),
            Column::callback('created_by', function ($created_by) {
                $row = User::find($created_by);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Created By'),
            Column::name('created_at')->label('Created On'),
            Column::callback('status_id', function ($status_id) {
                $row = StatusCase::find($status_id);
                if ($row) {
                    return $row->status_name;
                }
                return '-';
            })->label('Status'),

            Column::callback(['id'], function ($id) {
                return 'case aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataCaseById', $id);
    }

    public function chatWA($wa = null)
    {
        $this->emit('chatWA', $wa);
    }

    public function getDetailById($id)
    {
        $this->emit('getDetailById', $id);
    }

    public function getId($id)
    {
        $this->emit('getCaseId', $id);
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

    public function applyFilter($filters = [])
    {
        $this->filters = $filters;
    }
}
