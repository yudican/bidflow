<?php

namespace App\Console\Commands;

use App\Models\OrderLead;
use App\Models\ProductNeed;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;

class ReminderOrderLeadPaymentTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:lead-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orders = OrderLead::whereNotNull('payment_term')->get();
        foreach ($orders as $key => $order) {
            if ($order->paymentTerm->days_of > 1) {
                $earlier = new DateTime(date('Y-m-d'));
                $later = new DateTime($order->due_date);
                $abs_diff = $later->diff($earlier)->format("%a"); //3

                if ($abs_diff == 7) {
                    return $this->sendNotification($order, 'AGOR7200');
                } else if ($abs_diff == 3) {
                    return $this->sendNotification($order, 'AGOR3200');
                } else if ($abs_diff == 1) {
                    return $this->sendNotification($order, 'AGOR1200');
                } else {
                    $order->update(['grace_due_date' => Carbon::now()->addDays(7)]);
                    $this->sendNotification($order, 'AGOGP200');
                    return $this->sendNotification($order, 'AGO0200');
                }
            }
        }
        return 0;
    }

    public function sendNotification($row, $code)
    {
        $productNeeds = ProductNeed::where('uid_lead', $row->uid_lead)->get();
        return createNotification(
            $code,
            [
                'user_id' => $row->sales
            ],
            [
                'user' => $row->salesUser->name,
                'order_number' => $row->order_number,
                'title_order' => $row->title,
                'created_on' => $row->created_at,
                'contact' => $row->contactUser->name,
                'assign_by' => $row->createUser->name,
                'status' => 'Reminder',
                'courier_name' => $row->courierUser ? $row->courierUser->name : '-',
                'receiver_name' => $row->addressUser ? $row->addressUser->nama : '-',
                'shipping_address' => $row->addressUser ? $row->addressUser->alamat_detail : '-',
                'detail_product' => detailProductOrder($productNeeds),
                'grace_date' => $row->grace_due_date
            ],
            ['brand_id' => $row->brand_id]
        );
    }
}
