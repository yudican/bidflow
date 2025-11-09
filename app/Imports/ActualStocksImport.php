<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ActualStocksImport implements ToCollection, WithHeadingRow
{
  public function collection(Collection $rows)
  {
    foreach ($rows as $row) {
      // Handle date conversion
      $date = $row['date'];
      if (is_numeric($date)) {
        // If it's a serial number from Excel
        $date = Date::excelToDateTimeObject($date)->format('Y-m-d');
      } else {
        // If it's a string, try to parse it
        try {
          $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
          $date = now()->format('Y-m-d'); // fallback to today
        }
      }

      DB::connection('pgsql')->table('accurate_actual_stocks')->insert([
        'count_id' => $row['count_id'],
        'date' => $date,
        'customer_id' => $row['customer_id'],
        'product_code' => $row['product_code'],
        'actual_stock' => $row['actual_stock'],
        'pic_name' => $row['pic_name'],
        'notes' => $row['notes'],
        'key' => $row['key'],
        'upload_by' => Auth::user()->name ?? 'System',
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
