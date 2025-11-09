<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\TransactionAgent;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionReportExportTable implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
  protected $filters = [];

  public function __construct($filters)
  {
    $this->filters = $filters;
  }

  public function query()
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

  public function map($row): array
  {
    return [
      $row->id_transaksi,
      $row->user->name,
      'Rp ' . number_format($row->nominal),
      $row->paymentMethod->nama_bank,
      $row->diskon ? 'Rp ' . number_format($row->diskon) : 0,
      $row->created_at,
      $this->_getStatus($row->status),
      $this->_getStatusDelivery($row->status_delivery),
    ];
  }

  public function headings(): array
  {
    return [
      'Trx ID',
      'User',
      'Nominal',
      'Metode Pembayaran',
      'Diskon',
      'Tanggal Transaksi',
      'Status',
      'Status Pengiriman',
    ];
  }

  public function columnFormats(): array
  {
    return [
      'C' => NumberFormat::FORMAT_NUMBER
    ];
  }


  /**
   * @return string
   */
  public function title(): string
  {
    return 'Laporan transaksi';
  }

  public function _getStatus($status)
  {
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
  }

  public function _getStatusDelivery($status_delivery)
  {
    switch ($status_delivery) {
      case 1:
        return 'Waiting Process';
        break;
      case 2:
        return 'Proses Packing';
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
  }
}
