<?php

namespace App\Console\Commands;

use App\Models\LeadActivity;
use App\Models\LeadMaster;
use Illuminate\Console\Command;

class LeadActivityReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lead Activity Reminder';

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
        $lead_activities = LeadActivity::all();
        $now = strtotime(date('Y-m-d H:i:s'));
        foreach ($lead_activities as $activity) {
            $lead_master = LeadMaster::where('uid_lead', $activity->uid_lead)->first();
            $start_date = strtotime($activity->start_date);
            $end_date = strtotime($activity->end_date);

            if ($now < $end_date && $now > $start_date) {
                createNotification(
                    'ALER200',
                    [],
                    [
                        'sales' => $lead_master->salesUser->name,
                        'assign_by' => 'Admin',
                        'lead_title' => $lead_master->title,
                        'date_assign' => $lead_master->created_at,
                        'contact' => $lead_master->contactUser->name,
                        'company' => $lead_master->brand->name,
                        'status_lead' => getStatusLead($lead_master->status),
                    ],
                    ['brand_id' => $lead_master->brand_id]
                );
            } else if ($now > $end_date) {
                $activity->update(['status' => 3]);
                createNotification(
                    'ALEXP200',
                    [],
                    [
                        'sales' => $lead_master->salesUser->name,
                        'assign_by' => 'Admin',
                        'lead_title' => $lead_master->title,
                        'date_assign' => $lead_master->created_at,
                        'contact' => $lead_master->contactUser->name,
                        'company' => $lead_master->brand->name,
                        'status_lead' => getStatusLead($lead_master->status),
                    ],
                    ['brand_id' => $lead_master->brand_id]
                );
            }
        }
    }
}
