<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Price;
use App\Models\Level;
use App\Models\Product;
use App\Models\ProductVariant;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class PriceTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_prices';
    public $hide = [];

    public function builder()
    {
        return Price::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::callback(['prices.level_id'], function ($level_id) {
                $level = Level::find($level_id);
                return $level->name;
            })->label('Level'),
            Column::callback(['prices.product_id'], function ($product_id) {
                $product = Product::find($product_id);
                return $product->name;
            })->label('Product')->searchable(),
            Column::callback(['prices.product_variant_id'], function ($product_variant_id) {
                $productv = ProductVariant::find($product_variant_id);
                return @$productv->name;
            })->label('Product Variant')->searchable(),
            Column::callback(['basic_price'], function ($basic_price) {
                return "Rp " . number_format($basic_price, 0, ',', '.');
            })->label('Basic Price'),
            Column::callback(['final_price'], function ($final_price) {
                return "Rp " . number_format($final_price, 0, ',', '.');
            })->label('Final Price'),
            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataPriceById', $id);
    }

    public function getId($id)
    {
        $this->emit('getPriceId', $id);
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
