<?php

namespace App\Jobs;

use App\Models\LeadReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveReminderActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request = [
        'reminder_id' => null,
        'field' => null,
        'value' => null,
        'uid_lead' => null,
    ];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        if (isset($request['reminder_id'])) {
            $reminder = LeadReminder::find($request['reminder_id']);
            $reminder->update([$request['field'] => $request['value']]);
        } else {
            $data = [
                'uid_lead' => $request['uid_lead'],
                $request['field'] => $request['value']
            ];
            LeadReminder::create($data);
        }

        return true;
    }
}
