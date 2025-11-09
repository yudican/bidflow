<?php

namespace App\Exports\Sample;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductSkuSample implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'TRX ID',
            'USER',
            'CHANNEL',
            'NAMA TOKO',
            'SKU',
            'NAMA PRODUK',
            'HARGA AWAL',
            'HARGA PROMOSI',
            'QTY',
            'NOMINAL',
            'BIAYA PENGIRIMAN',
            'Pajak',
            'Asuransi',
            'Total Diskon',
            'Biaya Komisi',
            'Biaya Layanan',
            'Ongkir Dibayar Sistem',
            'Potongan Harga',
            'Subsidi angkutan',
            'Koin',
            'Koin Cashback Penjual',
            'Jumlah Pengembalian Dana',
            'Voucher Channel',
            'Diskon Penjual',
            'Biaya layanan kartu kredit',
            'METODE PEMBAYARAN',
            'DISKON',
            'TANGGAL TRANSAKSI',
            'KURIR',
            'KODE PELACAK',
            'STATUS',
            'STATUS PENGIRIMAN',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Sample';
    }
}
