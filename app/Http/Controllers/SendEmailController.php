<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotifications;
use Illuminate\Http\Request;

class SendEmailController extends Controller
{
    public function sendMailNotification($data = [])
    {
        SendNotifications::dispatch($data)->onQueue('queue-log');
    }

    public function sendMailNotificationApi(Request $request)
    {
        SendNotifications::dispatch($request->all())->onQueue('queue-log');
        return response()->json(['success' => true]);
        // try {
        //     // $view = $request->view ?? 'email-template';
        //     // $transaction = null;
        //     // if ($request->transaction_id) {
        //     //     $transaction = Transaction::find($request->transaction_id);
        //     // }
        //     // $cc = $request->cc ?? [];
        //     // Mail::send('email.crm.' . $view, ['body' => $request->body, 'date' => $request->date, 'type' => $request->type, 'actionUrl' => $request->actionUrl, 'invoice' => $transaction ? $transaction->id_transaksi : $request->invoice, 'price' => $request->price, 'payment_method' => $request->payment_method, 'transaction' => $transaction], function ($message) use ($request, $cc) {
        //     //     $message->from('admin@flimty.co', 'Flimty');
        //     //     $message->to($request->email);
        //     //     if (count($cc) > 0) {
        //     //         $message->cc($cc);
        //     //     }
        //     //     $message->subject($request->title);
        //     // });
        //     return response()->json(['success' => true]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     Log::error($th->getMessage());
        //     return response()->json(['success' => false, 'message' => $th->getMessage()], 400);
        // }
    }
}
