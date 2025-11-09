<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationToUserQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user;
    protected $voucher_code;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $voucher_code)
    {
        $this->user = $user;
        $this->voucher_code = $voucher_code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        createNotification('FLIMAPP-TRX-NEW-VCR', [
            'device_id' => $user->device_id,
            'user_id' => $user->id
        ], ['voucher_code' => $this->voucher_code]);
    }
}
