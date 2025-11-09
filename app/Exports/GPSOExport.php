<?php

namespace App\Exports;

use App\Models\ListOrderGpDetail;
use App\Models\SkuMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GPSOExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $item_id;
    protected $title;
    public function __construct($item_id, $title = 'ExportConverData')
    {
        $this->item_id = $item_id;
        $this->title = $title;
    }

    public function query()
    {
        return ListOrderGpDetail::query()->leftJoin('order_list_by_genies', 'order_list_by_genies.id', '=', 'list_order_gp_details.ginee_order_id')->where('list_order_gp_id', $this->item_id)->select('list_order_gp_details.so_number', 'order_list_by_genies.qty', 'order_list_by_genies.sku', 'order_list_by_genies.nominal', 'order_list_by_genies.pajak', 'order_list_by_genies.ongkir', 'order_list_by_genies.total_diskon', 'order_list_by_genies.nama_produk', 'order_list_by_genies.channel', 'order_list_by_genies.store', 'order_list_by_genies.biaya_komisi', 'order_list_by_genies.tanggal_transaksi')->selectRaw("SUM(tbl_order_list_by_genies.qty) as qty_total")->orderBy('order_list_by_genies.tanggal_transaksi', 'desc')->groupBy('sku');
    }

    public function map($row): array
    {
        return [
            $row->so_number,
            $row->tanggal_transaksi,
            $row->channel,
            $row->store,
            $row->sku,
            $row->nama_produk,
            $row->u_of_m,
            $row->qty_total,
            $row->extended_price,
            $row->freight_amount,
            $row->miscellaneous,
            $row->tax_amount,
            $row->total_diskon,
        ];
    }

    public function headings(): array
    {
        return [
            'Document Number',
            'Document Date',
            'Customer ID',
            'Customer Name',
            'Item Number',
            'Item Description',
            'U of M',
            'Quantity',
            'Extended Price',
            'Freight Amount',
            'Miscellaneous',
            'Tax Amount',
            'Trade Discount',
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
