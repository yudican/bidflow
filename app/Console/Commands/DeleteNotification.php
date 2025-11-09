<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Notification After 7 Days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Notification::where('created_at', '<=', Carbon::now()->subDays(7))->delete();
        return Command::SUCCESS;
    }
}
