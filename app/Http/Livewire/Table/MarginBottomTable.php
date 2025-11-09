<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\MarginBottom;
use App\Models\Product;
use App\Models\ProductVariant;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class MarginBottomTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_product_margin_bottoms';
    public $hide = [];

    public function builder()
    {
        return MarginBottom::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::callback('product_variant_id', function ($product_variant_id) {
                $row = ProductVariant::find($product_variant_id);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Variant'),
            Column::name('basic_price')->label('Final Price')->searchable(),
            // Column::name('role_id')->label('Role Id')->searchable(),
            Column::name('margin')->label('Margin')->searchable(),
            // Column::name('description')->label('Description')->searchable(),
            // Column::name('status')->label('Status')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataMarginBottomById', $id);
    }

    public function getId($id)
    {
        $this->emit('getMarginBottomId', $id);
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
