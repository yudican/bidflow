<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Price;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\InventoryItem;

class ProductTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_products';
    public $hide = [];

    public function builder()
    {
        return Product::query()->whereNull('deleted_at');
    }

    public function columns()
    {
        // $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::callback([$this->table_name . '.name', $this->table_name . '.id'], function ($name, $product_id) {
                $product = Product::find($product_id);
                if (count($product->categories) > 0) {
                    return $product->categories->implode('name', ', ');
                }
                return '-';
            })->label('Category'),
            Column::callback(['brand_id'], function ($brand_id) {
                $brand = Brand::find($brand_id);
                // set - is value null
                if ($brand) {
                    return $brand->name;
                } else {
                    return '-';
                }
            })->label('Brand')->searchable(),
            Column::name('name')->label('Product Name')->searchable(),
            Column::name('slug')->label('Slug'),
            Column::name('description')->label('Description')->hide(),
            Column::callback('image', function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => getImage($image),
                ]);
            })->label(__('Image')),
            Column::callback(['tbl_products.id', 'tbl_products.category_id'], function ($id, $category_id) {
                $price = Price::where('product_id', $id)->where('level_id', 7)->first();
                if ($price) {
                    return "Rp " . number_format($price->basic_price, 0, ',', '.');
                }
                return 'Rp. 0';
            })->label('Customer Price'),
            Column::callback(['tbl_products.id', 'tbl_products.discount_price'], function ($id, $discount_price) {
                $price = Price::where('product_id', $id)->where('level_id', 7)->first();
                if ($price) {
                    return "Rp " . number_format($price->final_price, 0, ',', '.');
                }
                return 'Rp. 0';
            })->label('Discount Price'),
            Column::callback([$this->table_name . '.stock', $this->table_name . '.id'], function ($stock, $product_id) {
                $product = Product::find($product_id);
                $total_stock = 0;
                foreach ($product->variants as $key => $value) {
                    $stock_item = InventoryItem::where('product_id', $value->id)->whereHas('inventoryStock', function ($query) {
                        return $query->whereIn('status', ['ready', 'done']);
                    })->where('type', 'stock')->sum('qty');
                    $total_stock += $stock_item;
                }
                return $total_stock;
            })->label('Stock'),
            // Column::name('weight')->label('Weight'),
            // Column::name('is_varian')->label('Is Varian'),
            Column::callback('status', function ($status) {
                if ($status == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Status'),

            Column::callback(['tbl_products.id', 'tbl_products.status'], function ($id, $status) {
                return '<div><button class="btn btn-warning btn-sm mr-2" wire:click="getCommentById(' . $id . ')" id="btn-edit-' . $id . '"><i class="fas fa-eye"></i> Lihat Detail</button></div>';
            })->label('Rating & Comment'),

            Column::callback(['id'], function ($id) {

                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataProductById', $id);
    }

    public function getCommentById($id)
    {
        $this->emit('getCommentById', $id);
    }

    public function getStockById($id)
    {
        $this->emit('getStockById', $id);
    }

    public function getId($id)
    {
        $this->emit('getProductId', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }
}
