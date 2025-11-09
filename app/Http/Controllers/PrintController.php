<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\OrderTransfer;
use App\Models\AddressUser;
use App\Models\InventoryProductReturn;
use App\Models\InventoryDetailItem;
use App\Models\InventoryItem;
use App\Models\InventoryProductStock;
use App\Models\ProductNeed;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\LogApproveFinance;
use App\Models\LogPrintOrder;
use App\Models\OrderDelivery;
use App\Models\TransactionLabel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PDF;
use DNS1D;
use Illuminate\Support\Facades\DB;
use Livewire\ComponentConcerns\ReceivesEvents;

class PrintController extends Controller
{
    use ReceivesEvents;

    public function printSo($uid_lead = null)
    {
        if (auth()->check()) {
            $lead = OrderManual::where('uid_lead', $uid_lead)->first();
            $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', $lead->contact)->first();
            }
            $productneeds = DB::table('vw_sales_orders_items')->where('uid_lead', $uid_lead)->get();
            // echo"<pre>";print_r($lead);die();
            try {
                DB::beginTransaction();
                DB::table('order_manuals')->where('uid_lead', $uid_lead)->update(['print_status' => 'printed']);
                LogPrintOrder::create([
                    'uid_lead' => $uid_lead,
                    'user_id' => auth()->user()->id,
                    'type' => 'so'
                ]);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }
            $delivery = DB::table('order_deliveries')->where('uid_lead', $uid_lead)->select('delivery_date')->first();
            return view('print.so', ['lead' =>  $lead, 'mainaddress' => $main, 'productneeds' => $productneeds, 'delivery' => $delivery, 'total_print' => LogPrintOrder::where('uid_lead', $uid_lead)->whereType('so')->count()]);
        }

        return redirect('/login/dashboard');
    }

    public function printSi($uid_lead = null, $product_need_ids = null)
    {
        if (auth()->check()) {
            $sales_order = OrderManual::where('uid_lead', $uid_lead)->first();
            $main = AddressUser::where('user_id', $sales_order->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', $sales_order->contact)->first();
            }

            $invoice_number = $sales_order->invoice_number;
            $productneeds = [];
            if ($product_need_ids) {
                $deliv = DB::table('order_deliveries')->where('uid_invoice', $product_need_ids)->select(['invoice_number', 'uid_invoice'])->first();
                if ($deliv) {
                    if ($deliv->uid_invoice) {
                        $productneeds = DB::table('vw_sales_orders_delivery_items')->where('uid_invoice', $deliv->uid_invoice)->get();
                    } else {
                        $productneeds = DB::table('vw_sales_orders_delivery_items')->where('uid_lead', $uid_lead)->get();
                    }
                }
                $invoice_number = $deliv ? $deliv->invoice_number : $invoice_number;
            } else {
                $productneeds = DB::table('vw_sales_orders_items')->where('uid_lead', $uid_lead)->get();
            }
            try {
                DB::beginTransaction();
                DB::table('order_manuals')->where('uid_lead', $uid_lead)->update(['print_status' => 'printed']);
                LogPrintOrder::create([
                    'uid_lead' => $uid_lead,
                    'user_id' => auth()->user()->id,
                    'type' => 'si'
                ]);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }

            $invoice = $product_need_ids ?  DB::table('vw_sales_orders_invoices')->where('uid_lead', $uid_lead)->first() : null;
            $delivery = DB::table('order_deliveries')->where('uid_lead', $uid_lead)->select('delivery_date')->first();
            return view('print.si', ['lead' =>  $sales_order, 'invoice' => $invoice, 'invoice_number' => $invoice_number, 'mainaddress' => $main, 'productneeds' => $productneeds, 'delivery' => $delivery, 'single' => $product_need_ids ? true : false, 'total_print' => LogPrintOrder::where('uid_lead', $uid_lead)->where('type', 'si')->count()]);
        }

        return redirect('/login/dashboard');
    }

    public function printSj($uid_lead = null, $delivery_id = null)
    {
        if (auth()->check()) {
            if ($delivery_id) {
                $orderDelivery = OrderDelivery::where('id', $delivery_id)->first(['invoice_number', 'delivery_date']);
                $lead = null;
                $leadOrder = OrderLead::query();

                $lead = $leadOrder->where('uid_lead', $uid_lead)->first();

                if (!$lead) {
                    $orderManual = OrderManual::query();

                    $lead = $orderManual->where('uid_lead', $uid_lead)->first();
                }

                $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
                if (empty($main)) {
                    $main = AddressUser::where('user_id', $lead->contact)->first();
                }

                // if (!$lead->delivery_date) {
                //     $lead->update(['delivery_date' => date('Y-m-d')]);
                // }

                $productneed = OrderDelivery::where('id', $delivery_id)->where('uid_lead', $uid_lead)->where('status', '!=', 'cancel');


                $productneeds = $productneed->get();

                $lead->update(['print_status' => 'printed']);
                $sj_type = 'sj-' . $delivery_id;
                LogPrintOrder::create([
                    'uid_lead' => $uid_lead,
                    'user_id' => auth()->user()->id,
                    'type' => $sj_type
                ]);
                return view('print.sj', ['lead' =>  $lead, 'productneeds' => $productneeds, 'mainaddress' => $main, 'delivery' =>  $orderDelivery, 'total_print' => LogPrintOrder::where('uid_lead', $uid_lead)->whereType($sj_type)->count()]);
            }

            $orderDelivery = OrderDelivery::where('uid_lead', $uid_lead)->where('status', '!=', 'cancel')->first(['invoice_number', 'delivery_date']);

            $lead = null;
            $leadOrder = OrderLead::query();

            $lead = $leadOrder->where('uid_lead', $uid_lead)->first();

            if (!$lead) {
                $orderManual = OrderManual::query();

                $lead = $orderManual->where('uid_lead', $uid_lead)->first();
            }

            $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', $lead->contact)->first();
            }

            // if (!$lead->delivery_date) {
            //     $lead->update(['delivery_date' => date('Y-m-d')]);
            // }

            $productneed = OrderDelivery::where('uid_lead', $uid_lead)->where('status', '!=', 'cancel');


            $productneeds = $productneed->get();

            $lead->update(['print_status' => 'printed']);
            LogPrintOrder::create([
                'uid_lead' => $uid_lead,
                'user_id' => auth()->user()->id,
                'type' => 'sj'
            ]);
            return view('print.sj', ['lead' =>  $lead, 'productneeds' => empty($productneeds) ? ProductNeed::where('uid_lead', $uid_lead)->get() : $productneeds, 'mainaddress' => $main, 'delivery' =>  $orderDelivery, 'total_print' => LogPrintOrder::where('uid_lead', $uid_lead)->whereType('sj')->count()]);
        }

        return redirect('/login/dashboard');
    }

    public function printSr($uid_retur = null)
    {
        $lead = SalesReturn::where('uid_retur', $uid_retur)->first();
        $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
        if (empty($main)) {
            $main = AddressUser::where('user_id', $lead->contact)->first();
        }
        $productneeds = SalesReturnItem::where('uid_retur', $uid_retur)->get();


        return view('print.sr', ['lead' =>  $lead, 'mainaddress' => $main, 'productneeds' => $productneeds]);
    }

    public function printSpr($uid_inventory = null)
    {
        $inventory = InventoryProductReturn::where('uid_inventory', $uid_inventory)->first();

        return view('print.spr', ['data' =>  $inventory]);
    }

    public function printInvoice($uid_retur = null)
    {
        $lead = SalesReturn::where('uid_retur', $uid_retur)->first();
        $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
        if (empty($main)) {
            $main = AddressUser::where('user_id', $lead->contact)->first();
        }
        $productneeds = SalesReturnItem::where('uid_retur', $uid_retur)->get();


        return view('print.sj', ['lead' =>  $lead, 'mainaddress' => $main, 'productneeds' => $productneeds]);
    }

    public function printTransfer($uid_inventory = null)
    {
        $transfer = InventoryProductStock::with(['historyAllocations', 'historyAllocations.product', 'historyAllocations.fromWarehouse', 'historyAllocations.toWarehouse'])->where('uid_inventory', $uid_inventory)->where('inventory_type', 'transfer')->first();

        if (!$transfer) {
            return abort(404, 'Data Tidak Ditemukan');
        }

        return view('print.transfer', ['transfer' =>  $transfer]);
    }

    public function printLabelTransaction($id_transaksi = null)
    {
        $id_transaksis = explode(',', $id_transaksi);
        $transaction = Transaction::with('transactionDetail')->whereIn('id_transaksi', $id_transaksis)->get();
        $transaction_labels = TransactionLabel::whereIn('id_transaksi', $id_transaksis)->get();
        foreach ($transaction_labels as $key => $label) {
            $label->update(['status' => 1]);
        }
        foreach ($transaction as $key => $trans) {
            if ($trans->status_delivery == 21) {
                $trans->update(['status_delivery' => 3]);
            }
            if ($trans->invoice_number) {
                $trans->update(['status_label' => 1]);
            } else {
                $trans->update(['status_label' => 1, 'invoice_number' => Transaction::generateInvoiceNumberNumber(), 'invoice_date' => Carbon::now()]);
            }
        }

        if (!$transaction) {
            return abort(404, 'Data Tidak Ditemukan');
        }

        return view('print.transaction_label', ['transactions' =>  $transaction]);
    }

    public function printPdf($id)
    {
        $data = array(
            'token' => 'sdfvgsw48rty3s4o98tye43o5897yt4o9esw7yt',
            'id' => $id,
            'endpoint' => 'dev.daftar-agen.com'
        );

        $payload = json_encode($data);

        // Prepare new cURL resource
        $ch = curl_init('https://us-south.functions.appdomain.cloud/api/v1/web/amandacarolineze_aimi2022/default/pdfss.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            )
        );

        // Submit the POST request
        $result = curl_exec($ch);
        // Close cURL session handle
        curl_close($ch);

        $final = json_decode($result);

        if (empty($final->success)) {
            return "ID Tidak di temukan pada endpoint dev.daftar-agen.com";
        } else {

            return redirect($final->url);
        }
    }

    public function printSoKons($uid_inventory = null)
    {
        if (auth()->check()) {
            $lead = OrderTransfer::where('uid_inventory', $uid_inventory)->first();

            $main = AddressUser::where('user_id', @$lead->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', @$lead->contact)->first();
            }
            $productneeds = InventoryDetailItem::where('uid_inventory', $uid_inventory)->get();
            // $lead->update(['print_status' => 'printed']);
            LogPrintOrder::create([
                'uid_lead' => $uid_inventory,
                'user_id' => auth()->user()->id
            ]);

            return view('print.sok', ['lead' =>  $lead, 'mainaddress' => $main, 'productneeds' => $productneeds]);
        }

        return redirect('/login/dashboard');
    }

    public function printSjKons($uid_inventory = null)
    {
        if (auth()->check()) {

            $lead = null;
            // $leadOrder = OrderLead::query();
            // $lead = $leadOrder->where('uid_lead', $uid_lead)->first();
            $lead = OrderTransfer::where('uid_inventory', $uid_inventory)->first();

            if (!$lead) {
                $orderManual = OrderManual::query();

                $lead = $orderManual->where('uid_lead', $uid_lead)->first();
            }
            $orderDelivery = '';
            // $orderDelivery = OrderDelivery::where('uid_delivery', $delivery_id)->first(['invoice_number', 'delivery_date']);
            $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', $lead->contact)->first();
            }

            // if (!$lead->delivery_date) {
            //     $lead->update(['delivery_date' => date('Y-m-d')]);
            // }

            // $productneed = OrderDelivery::where('uid_delivery', $delivery_id);
            // $productneeds = $productneed->get();

            $productneeds = InventoryDetailItem::where('uid_inventory', $uid_inventory)->get();

            // $lead->update(['print_status' => 'printed']);
            LogPrintOrder::create([
                'uid_lead' => $uid_inventory,
                'user_id' => auth()->user()->id
            ]);
            return view('print.sjk', ['lead' =>  $lead, 'productneeds' => $productneeds, 'mainaddress' => $main, 'delivery' =>  $orderDelivery]);
        }

        return redirect('/login/dashboard');
    }

    public function printAdjust($uid_inventory = null)
    {
        if (auth()->check()) {
            $lead = null;
            $lead = InventoryProductStock::where('uid_inventory', $uid_inventory)->first();

            $productneeds = InventoryDetailItem::where('uid_inventory', $uid_inventory)->get();
            // echo"<pre>";print_r($lead);die();
            // $lead->update(['print_status' => 'printed']);
            LogPrintOrder::create([
                'uid_lead' => $uid_inventory,
                'user_id' => auth()->user()->id
            ]);
            return view('print.adjust', ['lead' =>  $lead, 'productneeds' => $productneeds]);
        }

        return redirect('/login/dashboard');
    }

    public function printSiKons($uid_inventory = null)
    {
        if (auth()->check()) {
            $lead = OrderTransfer::where('uid_inventory', $uid_inventory)->first();

            $main = AddressUser::where('user_id', $lead->contact)->where('is_default', 1)->first();
            if (empty($main)) {
                $main = AddressUser::where('user_id', $lead->contact)->first();
            }

            $productneed = InventoryDetailItem::where('uid_inventory', $uid_inventory);

            $productneeds = $productneed->get();
            // echo"<pre>";print_r($lead);die();
            // $lead->update(['print_status' => 'printed']);
            // LogPrintOrder::create([
            //     'uid_lead' => $uid_lead,
            //     'user_id' => auth()->user()->id
            // ]);
            return view('print.sik', ['lead' =>  $lead, 'mainaddress' => $main, 'productneeds' => $productneeds, 'single' => true]);
        }

        return redirect('/login/dashboard');
    }
}
