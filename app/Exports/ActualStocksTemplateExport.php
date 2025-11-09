<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActualStocksTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
  public function array(): array
  {
    return [
      [
        'CNT001',
        '2025-01-15',
        'CUST001',
        'PROD001',
        '100',
        'John Doe',
        'Sample note',
        'SAMPLE001'
      ],
      [
        'CNT002',
        '2025-01-15',
        'CUST002',
        'PROD002',
        '50',
        'Jane Smith',
        'Another note',
        'SAMPLE002'
      ]
    ];
  }

  public function headings(): array
  {
    return [
      'Count_ID',
      'Date',
      'Customer_Code',
      'Barcode',
      'Actual_Stock',
      'PIC_Name',
      'Notes',
      'Key'
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      1 => ['font' => ['bold' => true]],
    ];
  }
}
