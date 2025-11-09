<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use App\Imports\ActualStocksImport;
use App\Exports\ActualStocksTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AccurateActualStocksController extends Controller
{
  public function index()
  {
    return view('spa.spa-index');
  }

  public function getActualStocks()
  {
    $data = DB::connection('pgsql')
      ->table('accurate_actual_stocks')
      ->orderBy('created_at', 'desc')
      ->paginate(100);

    return response()->json([
      'status' => 'success',
      'data' => $data,
    ]);
  }

  public function importActualStocks(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx,xls',
    ]);

    try {
      Excel::import(new ActualStocksImport, $request->file('file'));

      return response()->json([
        'status' => 'success',
        'message' => 'Data berhasil diimport',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Gagal import data: ' . $e->getMessage(),
      ], 500);
    }
  }

  // public function downloadTemplate()
  // {
  //   return Excel::download(new ActualStocksTemplateExport, 'template-import-actual-stocks.xlsx');
  // }

  public function downloadTemplate(Request $request)
  {
      $customerIds = $request->query('customers', []);
      if (empty($customerIds)) {
          return response()->json([
              'status' => 'error',
              'message' => 'Tidak ada customer dipilih'
          ], 400);
      }

      $products = \DB::table('accurate_products as p')
          ->join('accurate_customers as c', function ($join) {
              $join->on('p.customer_id', '=', 'c.id');
          })
          ->whereIn('c.id', $customerIds)
          ->select('c.name as Customer', 'p.product_code as Barcode', 'p.name as Product Name', 'p.unit', 'p.stock')
          ->get();

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Template Actual Stock');

      // Header
      $sheet->fromArray(['Customer', 'Barcode', 'Product Name', 'Unit', 'Stock'], NULL, 'A1');
      $sheet->fromArray($products->toArray(), NULL, 'A2');

      $writer = new Xlsx($spreadsheet);
      $filename = 'template-actual-stock.xlsx';
      $path = storage_path("app/public/$filename");
      $writer->save($path);

      return response()->download($path)->deleteFileAfterSend(true);
  }
}
