<?php

namespace App\Console\Commands;

use App\Models\OrderLead;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;

class ReminderOrderLeadGracePeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grace:period-order-lead';

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
        try {
            $orders = OrderLead::whereNotNull('payment_term')->get();
            foreach ($orders as $key => $order) {
                if ($order->paymentTerm->days_of > 1) {
                    $earlier = new DateTime(date('Y-m-d'));
                    $later = new DateTime($order->grace_due_date);
                    $abs_diff = $later->diff($earlier)->format("%a"); //3

                    if ($abs_diff == 1) {
                        return $this->sendNotification($order, 'AGOGP1200');
                    } else {
                        return $this->sendNotification($order, 'AGOGPE200');
                    }
                }
            }
        } catch (\Throwable $th) {
            return false;
        }
        return 0;
    }

    public function sendNotification($row, $code)
    {
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
                'detail_product' => detailProductOrder($row->productNeeds),
            ],
            ['brand_id' => $row->brand_id]
        );
    }
}
