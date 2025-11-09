<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GetDeviceIdFromUserQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $voucher_code;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($voucher_code)
    {
        $this->voucher_code = $voucher_code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = DB::table('users')
            ->whereNotNull('device_id')  // Hanya ambil user yang memiliki device_id
            ->select('device_id', 'email', 'id')         // Ambil hanya kolom device_id
            ->get();

        foreach ($users as $key => $user) {
            SendNotificationToUserQueue::dispatch($user, $this->voucher_code)->onQueue('queue-backend');
        }
    }
}
