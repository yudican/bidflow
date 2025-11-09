<?php

namespace App\Http\Livewire\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use PDF;

class ReportController extends Controller
{
  // export PDF
  public function index()
  {

    $data = Transaction::all();
    echo "<pre>";
    print_r($data);
    die();
    // $pdf = PDF::loadView('documents.transaction', ['data' => $data]);

    // return $pdf->download('Transaction.pdf');

  }
}
