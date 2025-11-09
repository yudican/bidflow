<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\TransactionDetail;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class InventoryTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_transaction_details';
    public $hide = [];

    public function builder()
    {
        return TransactionDetail::query()->leftjoin('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
        ->leftjoin('products', 'products.id', '=', 'transaction_details.product_id')->groupby('transaction_details.product_id')->where('transactions.user_id', auth()->user()->id);
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('product.name')->label('Product')->searchable(),
            Column::name('qty')->label('Qty'),
            Column::name('subtotal')->label('Subtotal'),
            Column::name('price')->label('Price'),

            Column::callback(['id'], function ($id) {
                $trans_det = TransactionDetail::find($id);
                return view('livewire.components.inventory-action-button', [
                    'id' => $id,
                    'product_id' => $trans_det->product_id,
                    'segment' => $this->params
                ]);
            })->label(__('Aksi')),
        ];
    }

    public function getDetailProductById($id)
    {
        $this->emit('getDetailProductById', $id);
    }

    public function getDetailTransById($id)
    {
        $this->emit('getDetailTransById', $id);
    }

    public function getDetailTrans($id)
    {
        $this->emit('getDetailTrans', $id);
    }

    public function getDataById($id)
    {
        $this->emit('getDataInventoryById', $id);
    }

    public function getId($id)
    {
        $this->emit('getInventoryId', $id);
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
