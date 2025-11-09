<?php

namespace App\Http\Livewire\Table;

use App\Exports\TransactionReportExportTable;
use App\Models\Transaction;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TransactionAgent;
use App\Models\LogApproveFinance;

class TransactionReportTable extends LivewireDatatable
{
  protected $listeners = ['refreshTable', 'applyFilter'];
  public $hideable = 'select';
  public $table_name = 'transactions.';
  public $hide = [];
  public $filters = [];

  public $exportable = true;

  public function builder()
  {
    if (isset($this->filters['query']) && isset($this->filters['created_at'])) {
      // if (isset($this->filters['transaction_type']) == 'agent') {
      //   return TransactionAgent::query()->where($this->filters['query'])->whereBetween('transaction_agents.created_at', $this->filters['created_at']);
      // }
      return Transaction::query()->where($this->filters['query'])->whereBetween('transactions.created_at', $this->filters['created_at']);
    } else if (isset($this->filters['query'])  && !isset($this->filters['created_at'])) {
      // if (isset($this->filters['transaction_type']) == 'agent') {
      //   return TransactionAgent::query()->where($this->filters['query']);
      // }
      return Transaction::query()->where($this->filters['query']);
    } else if (!isset($this->filters['query'])  && isset($this->filters['created_at'])) {
      // if (isset($this->filters['transaction_type']) == 'agent') {
      //   return TransactionAgent::query()->whereBetween('transaction_agents.created_at', $this->filters['created_at']);
      // }
      return Transaction::query()->whereBetween('transactions.created_at', $this->filters['created_at']);
    } else {
      return Transaction::query()->whereIn('transactions.status', [1, 2, 3, 7])->whereIn('transactions.status_delivery', [21, 3, 4]);
    }
  }

  public function columns()
  {
    return [
      Column::name('id')->label('No.')->width(5),
      Column::name('user.name')->label('User')->searchable(),
      Column::name('id_transaksi')->label('Trans ID')->searchable(),
      //Column::name('created_at')->label('TRX Date'),
      Column::callback(['created_at'], function ($transdate) {
        return date('l, d F Y, h:i:s', strtotime($transdate));
      })->label('Trans Date'),
      Column::name('paymentMethod.nama_bank')->label('Payment Method')->searchable()->hide(),
      // Column::name('product.name')->label('Product')->searchable(),

      Column::name('resi')->label('Resi')->searchable()->hide(),
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
      })->label('Status')->hide(),
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

  public function refreshTable()
  {
    $this->emit('refreshLivewireDatatable');
  }

  public function export()
  {
    return Excel::download(new TransactionReportExportTable($this->filters), 'data-transaction.xlsx');
  }

  public function applyFilter($data)
  {
    $this->filters = $data;
    $this->refreshTable();
  }
}
