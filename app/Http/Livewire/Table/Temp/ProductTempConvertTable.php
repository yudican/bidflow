<?php

namespace App\Http\Livewire\Table\Temp;

use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\ProductImportTemp;

class ProductTempConvertTable extends LivewireDatatable
{
  protected $listeners = ['refreshTable'];
  public $hideable = 'select';
  public $params = [];

  public function builder()
  {
    if ($this->params['type'] == 'all') {
      return ProductConvertDetail::query()->where('product_convert_id', $this->params['id']);
    }
    return ProductConvertDetail::query()->where('product_convert_id', $this->params['id'])->where('status_convert', $this->params['status']);
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.'),
      Column::name('sku')->label('sku')->searchable(),
      Column::name('produk_nama')->label('Nama Product')->searchable(),
      Column::name('qty')->label('qty')->searchable(),
      Column::name('toko')->label('toko')->searchable(),
      Column::name('harga_awal')->label('harga Produk')->searchable(),
      Column::name('harga_promo')->label('harga Promosi')->searchable(),
      Column::callback('harga_satuan', function ($harga_awal) {
        return $harga_awal;
      })->label('harga Satuan')->searchable(),
      Column::name('ongkir')->label('ongkir')->searchable(),
      Column::callback(['subtotal'], function ($subtotal) {
        return  $subtotal;
      })->label('Subtotal')->searchable(),
      Column::name('tanggal_transaksi')->label('tanggal transaksi')->searchable(),
      Column::callback('status_convert', function ($status) {
        if ($status == '1') {
          return '<span class="badge badge-success">Success</span>';
        } else {
          return '<span class="badge badge-danger">Gagal</span>';
        }
      })->label('status convert')->searchable(),
      // Column::callback(['tbl_product_convert_details.id', 'tbl_product_convert_details.status_convert'], function ($id, $status_convert) {
      //   if ($status_convert == 0) {
      //     return '<button class="btn btn-primary btn-sm" wire:click="showModalUpdate(' . $id . ')">Update</button>';
      //   }
      //   return '';
      // })->label(__('Aksi')),
    ];
  }

  public function showModalUpdate($id)
  {
    $this->emit('showModalUpdate', $id);
  }

  public function refreshTable()
  {
    $this->emit('refreshLivewireDatatable');
  }
}
