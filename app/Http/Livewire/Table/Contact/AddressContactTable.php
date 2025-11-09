<?php

namespace App\Http\Livewire\Table\Contact;

use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\AddressUser;

class AddressContactTable extends LivewireDatatable
{
  protected $listeners = ['refreshTable'];
  public $table_name = 'tbl_address_users';

  public function builder()
  {
    return AddressUser::query()->where('user_id', $this->params['user_id']);
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.'),
      Column::name('type')->label('Type')->searchable(),
      Column::name('nama')->label('Nama')->searchable(),
      Column::name('telepon')->label('Telepon')->searchable(),
      Column::name('alamat')->label('Alamat')->searchable(),

      Column::callback(['id'], function ($id) {
        return view('livewire.components.action-button', [
          'id' => $id,
          'segment' => $this->params['segment'],
          'extraActions' => [
            [
              'label' => 'Lihat Detail',
              'type' => 'default',
              'route' => 'getDetailAddress(' . $id . ')',
            ],
            [
              'label' => 'Set Default',
              'type' => 'default',
              'route' => 'setDefault(' . $id . ')',
            ],
          ]
        ]);
      })->label(__('Aksi')),
    ];
  }

  public function getDataById($id)
  {
    $this->emit('getDataAddressById', $id);
  }

  public function getId($id)
  {
    $this->emit('getId', $id);
  }

  public function getDetailAddress($id)
  {
    $this->emit('getDetailAddress', $id);
  }

  public function setDefault($id)
  {
    $this->emit('setDefault', $id);
  }

  public function refreshTable()
  {
    $this->emit('refreshLivewireDatatable');
  }
}
