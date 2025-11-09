<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params = [];
    protected $title = null;

    public function __construct($params = [], $title = 'Data Bagi Hasil List')
    {
        $this->params = $params;
        $this->title = $title;
    }

    public function query()
    {
        return Transaction::query()->where('status_delivery', 4)->where('status', 7);
    }

    public function map($row): array
    {
        return [
            $row->create_by_name,
            $row->user_name,
            $row->updated_at,
            $row->id_transaksi,
            $row->nominal,
            $row->dpp,
            $row->ppn,
            $row->total_pembagian,
            $row->akumulasi,
            $row->ign,
            $row->pph21,
            $row->nutrisionist_amount,
        ];
        
        
    }

    public function headings(): array
    {
        return [
            'Nama Nutrisionis',
            'Nama Customer',
            'Tanggal Transaksi',
            'No Invoice',
            'Jumlah Transaksi',
            'DPP',
            'PPN',
            'Total Pembagian',
            'Akumulasi',
            'Total Pembagian (IGN)',
            'PPH 21 Potong',
            'Total Pembayaran'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
