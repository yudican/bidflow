<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionExportTable implements FromView, ShouldAutoSize
{
    protected $params = [];
    protected $filters = [];

    public function __construct($params, $filters)
    {
        $this->params = $params;
        $this->filters = $filters;
    }

    public function view(): View
    {
        $transactions = Transaction::all();
        $transaction_data = [];

        foreach ($transactions as $key => $value) {
            // merge value same value
            $transaction_data[$key]['id_transaksi'] = $value->id_transaksi;
            $transaction_data[$key]['user'] = $value->user->name;
            $transaction_data[$key]['brand'] = $value->brand->name;
            $transaction_data[$key]['payment_method'] = $value?->paymentMethod?->nama_bank;
            $transaction_data[$key]['voucher'] = $value?->voucher?->voucher_code;
            $transaction_data[$key]['nominal'] = $value->nominal;
            $transaction_data[$key]['diskon'] = $value->diskon;
            $transaction_data[$key]['status'] = $this->_getStatus($value->status);
            $transaction_data[$key]['status_delivery'] = $this->_getStatusDelivery($value->status_delivery);
            $transaction_data[$key]['resi'] = $value->awb_number;
            $transaction_data[$key]['tanggal_transaksi'] = $value->created_at;
            $transaction_data[$key]['details'] = $value->transactionDetail()->get()->map(function ($item) {
                return [
                    'product_name' => $item?->productVariant?->name ?? '-',
                    'price' => $item->price,
                    'qty' => $item->qty,
                    'subtotal' => $item->subtotal,
                ];
            });
        }
        return view('export.transaction', [
            'data' => $transaction_data,
        ]);
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
