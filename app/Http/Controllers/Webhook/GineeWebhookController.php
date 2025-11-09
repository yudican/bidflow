<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\GetOrderDetailFromGenie;
use App\Models\OrderListByGenie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GineeWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        // try {
        //     DB::beginTransaction();
        //     if ($request['action'] == 'CREATE') {
        //         GetOrderDetailFromGenie::dispatch($request['payload']['orderId'], 0)->onQueue('queue-log');
        //     } else {
        //         $ginee = OrderListByGenie::where('trx_id', $request['payload']['orderId']);
        //         $ginee->update(['status' => $request['payload']['orderStatus']]);
        //     }

        //     DB::commit();
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     setSetting('ginee_webhook_msg', $th->getMessage());
        // }

        // return response()->json([
        //     'message' => 'success'
        // ]);
    }
}
