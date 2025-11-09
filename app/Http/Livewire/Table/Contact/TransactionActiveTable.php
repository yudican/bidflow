<?php

namespace App\Http\Livewire\Table\Contact;

use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\AddressUser;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\User;

class TransactionActiveTable extends LivewireDatatable
{
  protected $listeners = ['refreshTable'];
  public $table_name = 'tbl_address_users';

  public function builder()
  {
    $user = User::find($this->params['user_id']);
    if (in_array($user->role->role_type, ['agent', 'subagent'])) {
      return TransactionAgent::query()->where('status_delivery', '<', 4)->where('user_id', $user->id)->orderBy('created_at', 'desc');
    }
    return Transaction::query()->where('status_delivery', '<', 4)->where('user_id', $user->id)->orderBy('created_at', 'desc');
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.')->width(5),
      Column::name('user.name')->label('User')->searchable(),
      Column::name('id_transaksi')->label('Trans ID')->searchable(),
      Column::name('created_at')->label('Trans Date')->searchable(),
      Column::name('paymentMethod.nama_bank')->label('Payment Method'),
      Column::callback(['nominal'], function ($nominal) {
        return "Rp " . number_format($nominal, 0, ',', '.');
      })->label('Nominal'),
      Column::callback('status', function ($status) {
        switch ($status) {
          case 1:
            return 'Waiting Payment';
            break;
          case 2:
            return 'On Progress';
            break;
          case 3:
            return 'Success';
            break;
          case 4:
            return 'Cancel By System';
            break;
          case 5:
            return 'Cancel By User';
            break;
          case 6:
            return 'Cancel By Admin';
            break;
          case 7:
            return 'Admin Process';
            break;
          default:
            return 'Waiting Payment';
            break;
        }
      })->label('Status Pembayaran')->hide(),
      Column::callback('status_delivery', function ($status_delivery) {
        switch ($status_delivery) {
          case 1:
            return 'Waiting Process';
            break;
          case 2:
            return 'Proses Packing';
            break;
          case 21:
            return 'Siap Dikirim';
            break;
          case 3:
            return 'Sedang Dikirim';
            break;
          case 4:
            return 'Pesanan Diterima';
            break;
          case 5:
            return 'Pesanan Belum Diterima';
            break;
          case 6:
            return 'Pesanan Gagal';
            break;
          case 7:
            return 'Cancel By System';
            break;
          default:
            return 'Waiting Process';
            break;
        }
      })->label('Status Delivery')->hide(),
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
