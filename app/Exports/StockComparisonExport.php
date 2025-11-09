<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockComparisonExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows->map(function($r) {
            return [
                $r->head_account_no,
                $r->head_account,
                $r->subaccount_no,
                $r->subaccount,
                $r->item_no,
                $r->item_name,
                $r->stock_system ?? 0,
                $r->stock_actual ?? null,
                $r->difference ?? null,
                $r->status ?? '-',
                $r->gap_status ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Head Account No',
            'Head Account',
            'Subaccount No',
            'Subaccount',
            'Item No',
            'Item Name',
            'Stock System',
            'Stock Actual',
            'Difference',
            'Status',
            'Gap Status',
        ];
    }
}
