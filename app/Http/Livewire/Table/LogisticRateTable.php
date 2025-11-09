<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\LogisticRate;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class LogisticRateTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable', 'getLogisticID'];
    // public $hideable = 'select';
    public $table_name = 'tbl_logistic_rates';
    // public $hide = [];
    public $logistic_id;
    public function builder()
    {
        return LogisticRate::query()->where('logistic_id', 1);
    }

    public function columns()
    {
        return [
            Column::name('id')->label('No.'),
            Column::name('logistic.logistic_name')->label('Logistic'),
            Column::name('logistic_rate_code')->label('Logistic Rate Code'),
            Column::name('logistic_rate_name')->label('Logistic Rate Name'),
            BooleanColumn::name('logistic_rate_status')->label('Status'),
            BooleanColumn::name('logistic_cod_status')->label('Cod'),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataLogisticRateById', $id);
    }

    public function getLogisticID($id)
    {
        $this->logistic_id = $id;
        $this->emit('getDataLogisticById', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }

    // public function toggle($index)
    // {
    //     if ($this->sort == $index) {
    //         $this->initialiseSort();
    //     }

    //     $column = HideableColumn::where([
    //         'table_name' => $this->table_name,
    //         'column_name' => $this->columns[$index]['name'],
    //         'index' => $index,
    //         'user_id' => auth()->user()->id
    //     ])->first();

    //     if (!$this->columns[$index]['hidden']) {
    //         unset($this->activeSelectFilters[$index]);
    //     }

    //     $this->columns[$index]['hidden'] = !$this->columns[$index]['hidden'];

    //     if (!$column) {
    //         HideableColumn::updateOrCreate([
    //             'table_name' => $this->table_name,
    //             'column_name' => $this->columns[$index]['name'],
    //             'index' => $index,
    //             'user_id' => auth()->user()->id
    //         ]);
    //     } else {
    //         $column->delete();
    //     }
    // }
}
