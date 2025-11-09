<?php

namespace App\Http\Livewire\Table\Temp;

use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\ProductImportTemp;

class ProductTempImportTable extends LivewireDatatable
{
  protected $listeners = ['refreshTable'];
  public $hideable = 'select';

  public function builder()
  {
    return ProductImportTemp::query()->where('user_id', auth()->user()->id)->where('status_import', 0)->where('status_convert', 'failed');
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.'),
      Column::callback('status_convert', function ($status_convert) {
        if ($status_convert == 'failed') {
          return '<span class="badge badge-danger">' . $status_convert . '</span>';
        } else {
          return '<span class="badge badge-success">' . $status_convert . '</span>';
        }
      })->label('status convert')->searchable(),
      Column::callback('id', function ($id) {
        return '<button class="btn btn-primary btn-sm" wire:click="toggleModalUpdate(' . $id . ')">Edit</button>';
      })->label('aksi')->searchable(),
      Column::name('trx_id')->label('trx id')->searchable(),
      Column::name('user')->label('user')->searchable(),
      Column::name('channel')->label('channel')->searchable(),
      Column::name('toko')->label('toko')->searchable(),
      Column::name('sku')->label('sku')->searchable(),
      Column::name('produk_nama')->label('produk_nama')->searchable(),
      Column::name('harga_awal')->label('harga_awal')->searchable(),
      Column::name('harga_promo')->label('harga_promo')->searchable(),
      Column::name('qty')->label('qty')->searchable(),
      Column::name('ongkir')->label('ongkir')->searchable(),
      Column::name('metode_pembayaran')->label('metode_pembayaran')->searchable(),
      Column::name('diskon')->label('diskon')->searchable(),
      Column::name('tanggal_transaksi')->label('tanggal_transaksi')->searchable(),
      Column::name('kurir')->label('kurir')->searchable(),
      Column::name('resi')->label('resi')->searchable(),
      Column::name('status')->label('status')->searchable(),
    ];
  }

  public function toggleModalUpdate($id)
  {
    $this->emit('getProductImportTemp', $id);
    $this->emit('showModalImportUpdate', 'show');
  }

  public function refreshTable()
  {
    $this->emit('refreshLivewireDatatable');
  }
}
