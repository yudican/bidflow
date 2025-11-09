<?php

namespace App\Http\Controllers;

use App\Models\OrderLead;
use DateTime;
use Illuminate\Http\Request;

class GracePeriodController extends Controller
{
    public function check_duedate($uid_lead = null)
    {
        $lead = OrderLead::all();
        if (!empty($lead)) {
            foreach ($lead as $row) {
                $date_now = new DateTime();
                $date2    = new DateTime($row->due_date);
                $end_period = date('Y-m-d', strtotime($row->due_date . ' + 10 days'));

                if ($row->status == 2 && ($date_now > $date2 && $date_now < $end_period)) {
                    $update = OrderLead::find($row->id);
                    $update->status_invoice = 4;
                    $update->update();
                }
            }
        }
        return 'update status';
    }
}
