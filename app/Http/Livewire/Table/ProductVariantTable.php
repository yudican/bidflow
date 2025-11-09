<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\ProductVariant;
use App\Models\Package;
use App\Models\Variant;
use App\Models\Product;
use App\Models\Price;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\InventoryItem;

class ProductVariantTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_product_variants';
    public $hide = [];

    public function builder()
    {
        return ProductVariant::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('sku')->label('SKU'),
            Column::callback(['tbl_product_variants.sku', 'tbl_product_variants.sku_variant'], function ($sku, $sku_variant) {
                return "$sku_variant";
            })->label('SKU Variant'),
            Column::callback(['product_variants.product_id'], function ($product_id) {
                $row = Product::find($product_id);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Product')->searchable(),
            Column::callback(['product_variants.package_id'], function ($package_id) {
                $row = Package::find($package_id);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Package')->searchable(),
            Column::callback(['product_variants.variant_id'], function ($variant_id) {
                $row = Variant::find($variant_id);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Variant'),
            Column::name('name')->label('Name')->searchable(),
            Column::name('slug')->label('Slug'),
            Column::name('description')->label('Description'),
            Column::callback(['image'], function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => getImage($image),
                ]);
            })->label(__('Image')),
            Column::callback(['product_variants.id'], function ($id) {
                // $row = Variant::find($variant_id);
                $row = Price::where('product_variant_id', $id)->where('level_id', 4)->first();
                return (!empty($row->basic_price) ? $row->basic_price : '-');
            })->label('Agent Price'),
            // Column::name('agent_price')->label('Agent Price'),
            Column::callback(['tbl_product_variants.stock', 'tbl_product_variants.id'], function ($stock, $id) {
                return InventoryItem::where('product_id', $id)->whereHas('inventoryStock', function ($query) {
                    return $query->whereIn('status', ['ready', 'done']);
                })->where('type', 'stock')->sum('qty');
            })->label('Stock'),
            Column::name('weight')->label('Weight'),
            Column::callback(['product_variants.status'], function ($status) {
                if ($status == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Status'),

            Column::callback(['id'], function ($id) {
                return view('livewire.components.product-variant-action-button', [
                    'id' => $id,
                    'segment' => $this->params
                ]);
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataProductVariantById', $id);
    }

    public function getId($id)
    {
        $this->emit('getProductVariantId', $id);
    }

    public function getStockById($id)
    {
        $this->emit('getStockById', $id);
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
