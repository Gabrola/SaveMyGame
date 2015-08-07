<?php

namespace App\Console;

use App\Models\MonitoredUser;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\DownloadReplay::class,
        \App\Console\Commands\CheckSummoners::class,
        \App\Console\Commands\UpdateStatic::class,
        \App\Console\Commands\Test::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 0]);
        })->cron('0/5 * * * *')->name('check_summoners_0')->withoutOverlapping();

        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 1]);
        })->cron('1/5 * * * *')->name('check_summoners_1')->withoutOverlapping();

        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 2]);
        })->cron('2/5 * * * *')->name('check_summoners_2')->withoutOverlapping();

        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 3]);
        })->cron('3/5 * * * *')->name('check_summoners_3')->withoutOverlapping();

        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 4]);
        })->cron('4/5 * * * *')->name('check_summoners_4')->withoutOverlapping();

        if(\App::environment() == 'local') {
            $schedule->call(function () {
                \Artisan::call('replay:static');
            })->daily();
        }

        $schedule->call(function(){
            $hourAgo = Carbon::now()->subHour()->toDateTimeString();
            MonitoredUser::whereConfirmed(false)->where('created_at', '<=', $hourAgo)->delete();
        })->hourly();
    }
}
