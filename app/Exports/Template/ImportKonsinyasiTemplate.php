<?php

namespace App\Exports\Template;

use App\Models\OrderManual;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class ImportKonsinyasiTemplate implements FromView, ShouldAutoSize
{
    protected $data;
    protected $title;

    public function __construct($data, $title = 'ExportConverData')
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function view(): View
    {
        $data = $this->data;
        $mergedData = [];

        foreach ($data as $item) {
            // Buat kunci unik berdasarkan master_bin_id dan product_id
            $key = $item['master_bin_id'] . '-' . $item['product_id'];

            if (isset($mergedData[$key])) {
                // Jika kunci sudah ada, tambahkan qty
                $mergedData[$key]['qty'] += $item['qty'];
            } else {
                // Jika kunci belum ada, tambahkan data baru ke array
                $mergedData[$key] = $item;
            }
        }

        // Hasil array setelah digabung
        $mergedData = array_values($mergedData);

        return view('export.order-konsinyasi-import', [
            'data' => $mergedData,
            'expired_at' => Carbon::now()->addDay(7),
        ]);
    }

    public function title(): string
    {
        return $this->title;
    }
}
