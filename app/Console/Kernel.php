<?php

namespace App\Console;

use App\Console\Commands\CheckExpiredSKU;
use App\Console\Commands\CheckTransactionStatus;
use App\Console\Commands\CreatePopaketOrder;
use App\Console\Commands\DeleteNotification;
use App\Console\Commands\GenereateTokenPopaket;
use App\Console\Commands\GenereateTokenPopaketLogistic;
use App\Console\Commands\GetDataOrderByGenie;
use App\Console\Commands\GetGpToken;
use App\Console\Commands\GpRefreshToken;
use App\Console\Commands\LeadActivityReminder;
use App\Console\Commands\ReminderOrderLeadGracePeriod;
use App\Console\Commands\ReminderOrderLeadPaymentTerm;
use App\Console\Commands\ReminderProccessOrder;
use App\Console\Commands\TrackOrder;
use App\Console\Commands\UpdateAwbPopaket;
use App\Console\Commands\UpdateLinkStatusCommand;
use App\Console\Commands\UpdateStockMovementCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // LeadActivityReminder::class,
        // ReminderProccessOrder::class,
        GenereateTokenPopaket::class,
        GenereateTokenPopaketLogistic::class,
        GpRefreshToken::class,
        // CreatePopaketOrder::class,
        // TrackOrder::class,
        // UpdateAwbPopaket::class,
        // ReminderOrderLeadPaymentTerm::class,
        // ReminderOrderLeadGracePeriod::class,
        // GetDataOrderByGenie::class,
        // DeleteNotification::class,
        GetGpToken::class,
        CheckExpiredSKU::class,
        CheckTransactionStatus::class,
        UpdateLinkStatusCommand::class,
        UpdateStockMovementCommand::class,
        \App\Console\Commands\ImportAccurateSalesOrders::class,
        \App\Console\Commands\ImportAccurateItemTransfer::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('activity:reminder')->everyFourHours();
        // $schedule->command('reminderorder:proccess')->everySixHours();
        $schedule->command('popaket:token')->dailyAt('00:00');
        $schedule->command('logistic-popaket:token')->dailyAt('00:01');
        $schedule->command('log:stock-movement')->everyFourHours();
        // $schedule->command('order:track')->everyMinute();
        // $schedule->command('popaket:awb')->everyMinute();
        // $schedule->command('popaket:order')->everyMinute();
        // $schedule->command('ginie:order-list')->dailyAt('00:00');
        // $schedule->command('reminder:lead-order')->daily();
        // $schedule->command('grace:period-order-lead')->daily();
        // $schedule->command('notification:delete')->dailyAt('00:00');
        $schedule->command('gp:token')->everyTwoHours();
        $schedule->command('gp:refreshtoken')->everyMinute();
        $schedule->command('checkstatus:transaction')->everyMinute();
        // $schedule->command('check:expired-sku')->dailyAt('00:00');
        $schedule->command('update:link-status')->everyMinute();
        // $schedule->command('sync:accurate')->hourly();
        $schedule->command('accurate:import-warehouses')->dailyAt('00:00');
        $schedule->command('accurate:import-products')->dailyAt('00:00');
        $schedule->command('accurate:import-customers')->dailyAt('00:00');
        $schedule->command('accurate:import-sales-orders')->dailyAt('01:00');
        $schedule->command('accurate:import-item-transfer')->dailyAt('03:00')->timezone('Asia/Jakarta');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
