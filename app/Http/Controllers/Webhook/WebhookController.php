<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\LogError;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class WebhookController extends Controller
{
    public function runScript(Request $request)
    {
        // Artisan::call('popaket:token');
        // Artisan::call('activity:reminder');
        // Artisan::call('grace:period-order-lead');
        // Artisan::call('reminder:lead-order');
        // Artisan::call('reminderorder:proccess');
        // Artisan::call('popaket:awb');

        // LogError::updateOrCreate(['id' => 1], [
        //     'message' => 'Run Script Success',
        //     'trace' => json_encode($request->all()),
        //     'action' => 'Response Webhook',
        // ]);

        // return response()->json(['status' => 'success']);
    }

    public function assignToWarehouse(Request $request)
    {
        // Transaction::whereStatus(3)->update(['status' => 7, 'status_delivery' => 1]);
        // TransactionAgent::whereStatus(3)->update(['status' => 7, 'status_delivery' => 1]);
        // LogError::updateOrCreate(['id' => 1], [
        //     'message' => 'Run Script Success',
        //     'trace' => json_encode($request->all()),
        //     'action' => 'Response Webhook assignToWarehouse',
        // ]);
        // return response()->json(['status' => 'success']);
    }
}
