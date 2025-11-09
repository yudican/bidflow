<?php

namespace App\Providers;

use App\Models\CompanyAccount;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TransactionAgent;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\NotifAlert;
use Carbon\Carbon;
use Illuminate\Cache\NullStore;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Guard $auth)
    {
        // if (getSetting('ISPRODUCTION')) {
        //     URL::forceScheme('https');
        // }
        Cache::extend('none', function ($app) {
            return Cache::repository(new NullStore);
        });
        if (Schema::hasTable('teams')) {
            View::composer('*', function ($view) {
                if (auth()->check()) {
                    $team = Team::find(1);
                    $user = auth()->user();
                    $notification_user = $user->notification;
                    $notification_count_user = $user->notification_count;
                    $role = $user->role ?? null;
                    $global_user_id = $user->id ?? null;
                    $notification_user = $notification_user ?? [];
                    $notification_count_user = $notification_count_user ?? null;
                    $is_dark_mode = $user->dark_mode ?? false;


                    // pindah di helper


                    $view->with([
                        'curteam' => $team,
                        'role' => $role,
                        'global_user_id' => $global_user_id,
                        'notification_user' => $notification_user,
                        'notification_count_user' => $notification_count_user,
                        'is_dark_mode' => $is_dark_mode,
                        'accounts' => CompanyAccount::all(),
                        'notif' => NotifAlert::where('status', 1)->take(1)->get(),
                    ]);
                }
            });

            Schema::defaultStringLength(191);

            config(['app.locale' => 'id']);
            config(['filesystems.key' => getSetting('AWS_ACCESS_KEY')]);
            config(['filesystems.secret' => getSetting('AWS_SECRET_KEY')]);
            config(['filesystems.bucket' => getSetting('AWS_BUCKET')]);
            config(['filesystems.endpoint' => getSetting('AWS_ENDPOINT')]);
            config(['mailers.smtp.host' => getSetting('MAIL_HOST')]);
            config(['mailers.smtp.port' => getSetting('MAIL_PORT')]);
            config(['mailers.smtp.encryption' => getSetting('MAIL_ENCRYPTION')]);
            config(['mailers.smtp.username' => getSetting('MAIL_USERNAME')]);
            config(['mailers.smtp.password' => getSetting('MAIL_PASSWORD')]);

            Carbon::setLocale('id');
            //date_default_timezone_set('Asia/Jakarta');
            date_default_timezone_set(config('app.timezone'));
        }
    }
}
