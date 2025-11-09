<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use PDF;

class ReportController extends Controller
{
    public function __invoke() {
        $data = Transaction::leftjoin('users', 'transactions.user_id', '=', 'users.id')->get();
        
        $pdf = PDF::loadView('documents.transaction', ['data' => $data])->setPaper('A4', 'portrait');
        return $pdf->stream('Transaction.pdf');
        // echo"<pre>";print_r($data);die();
    }
}
