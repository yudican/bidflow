<?php

namespace App\Http\Livewire\Table\Temp;

use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\ProductImportTemp;

class ProductConvertTable extends LivewireDatatable
{
  protected $listeners = ['reloadTable'];
  public $hideable = 'select';
  public $params = 'all';
  public $loading = false;

  public function builder()
  {
    return ProductConvert::query();
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.'),
      Column::name('user.name')->label('User')->searchable(),
      Column::name('convert_date')->label('Tanggal')->searchable(),
      Column::callback(['tbl_product_converts.id', 'tbl_product_converts.convert_user_id'], function ($id, $converId) {
        return ProductConvertDetail::where('product_convert_id', $id)->where('status_convert', 0)->count();
      })->label('Error'),
      Column::callback(['tbl_product_converts.id', 'tbl_product_converts.created_at'], function ($id, $converId) {
        return ProductConvertDetail::where('product_convert_id', $id)->where('status_convert', 1)->count();
      })->label('Success'),
      Column::callback(['id'], function ($id) {
        $error =  ProductConvertDetail::where('product_convert_id', $id)->where('status_convert', 0)->count();
        if ($error > 0) {
          return '<a href="' . route('product.import') . '" class="btn btn-warning btn-sm">Perbaiki Data</a>';
        }
        return '<button class="btn btn-primary btn-sm" wire:click="showDetail(' . $id . ')">Detail</button>';
      })->label(__('Aksi')),
    ];
  }
  public function showDetail($id)
  {
    return redirect()->route('product.convert', ['id' => $id]);
  }

  public function reloadTable()
  {
    $this->emit('refreshLivewireDatatable');
  }
}
