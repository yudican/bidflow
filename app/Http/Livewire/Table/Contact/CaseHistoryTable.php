<?php

namespace App\Http\Livewire\Table\Contact;

use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\AddressUser;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\Cases;
use App\Models\User;

class CaseHistoryTable extends LivewireDatatable
{
  protected $listeners = ['refreshTable'];
  public $table_name = 'tbl_case_masters';

  public function builder()
  {
    $user = User::find($this->params['user_id']);
    return Cases::query()->where('contact', $user->id)->orderBy('created_at', 'desc');
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.')->width(5),
      Column::name('title')->label('Title')->searchable(),
      Column::name('created_at')->label('Created at')->searchable(),
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
