<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\SalesReturn;
use App\Models\User;
use App\Models\OrderLead;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class SalesReturnTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_sales_return_masters';
    public $hide = [];

    public function builder()
    {
        return SalesReturn::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('sr_number')->label('Sr Number')->searchable(),
            // Column::name('order_number')->label('Order Number')->searchable(),
            Column::callback('order_number', function ($order_number) {
                $order = OrderLead::find($order_number);
                if ($order) {
                    return $order->order_number;
                }
                return '-';
            })->label('Order Number'),
            Column::callback('contact', function ($contact) {
                $row = User::find($contact);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Contact'),
            Column::callback('sales', function ($sales) {
                $user = User::find($sales);
                if ($user) {
                    return $user->name;
                }
                return '-';
            })->label('Sales'),
            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataSalesReturnById', $id);
    }

    public function getId($id)
    {
        $this->emit('getSalesReturnId', $id);
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
