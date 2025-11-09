<?php

namespace App\Exports;

use App\Models\PurchaseRequitition;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseRequititionExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;
    protected $title;

    public function __construct($query, $title = 'Purchase Requisition List')
    {
        $this->query = $query;
        $this->title = $title;
    }

    public function query()
    {
        return $this->query;
    }

    public function map($row): array
    {
        $statusName = $this->getStatusName($row->request_status);

        // Format the created_at date to dd-mm-yyyy
        $formattedCreatedAt = (new \DateTime($row->created_at))->format('d-m-Y');

        // Handle the case when there are no items in the row
        if ($row->items->isEmpty()) {
            return [
                $row->id,
                $row->pr_number,
                $statusName,
                $row->created_by_name,
                $formattedCreatedAt,
                $row->item_url,
                $row->item_note,
                null, // No item name
                null, // No item quantity
            ];
        }

        // If there are items, map them
        $mappedRows = [];
        foreach ($row->items as $item) {
            $mappedRows[] = [
                $row->id,
                $row->pr_number,
                $statusName,
                $row->created_by_name,
                $formattedCreatedAt,
                $item->item_url,
                $item->item_note,
                $item->item_name,
                $item->item_qty,
            ];
        }

        return $mappedRows;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nomor PR',
            'Status',
            'Created By',
            'Created On',
            'Url',
            'Notes',
            'Product',
            'Qty',
        ];
    }

    /**
     * Get status name by status code
     *
     * @param int $status
     * @return string
     */
    private function getStatusName(int $status): string
    {
        switch ($status) {
            case 0:
                return 'Waiting Approval';
            case 1:
                return 'On Process';
            case 2:
                return 'Complete';
            case 3:
                return 'Rejected';
            default:
                return 'Draft';
        }
    }
}
