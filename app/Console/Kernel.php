<?php

namespace App\Console;

use App\Models\Chunk;
use App\Models\Game;
use App\Models\Keyframe;
use App\Models\MonitoredUser;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use LeagueHelper;

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
        })->cron('0/3 * * * *')->name('check_summoners_0')->withoutOverlapping();

        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 1]);
        })->cron('1/3 * * * *')->name('check_summoners_1')->withoutOverlapping();

        $schedule->call(function(){
            \Artisan::call('replay:check', ['batch' => 2]);
        })->cron('2/3 * * * *')->name('check_summoners_2')->withoutOverlapping();

        if(\App::environment() == 'local') {
            $schedule->call(function () {
                \Artisan::call('replay:static');
            })->daily();
        }

        $schedule->call(function(){
            $hourAgo = Carbon::now()->subHour()->toDateTimeString();
            MonitoredUser::whereConfirmed(false)->where('created_at', '<=', $hourAgo)->delete();
        })->everyFiveMinutes();

        if(app()->environment() == 'production')
        {
            $schedule->call(function () {
                $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
                $games = Game::where('created_at', '<', $sevenDaysAgo)->all();

                /** @var \App\Models\Game $game */
                foreach($games as $game)
                {
                    if (!$game->end_stats || LeagueHelper::comparePatch(config('clientversion', '0.0.0.0'), $game->end_stats['matchVersion']))
                    {
                        Chunk::byGame($game->platform_id, $game->game_id)->delete();
                        Keyframe::byGame($game->platform_id, $game->game_id)->delete();

                        $game->status = 'deleted';
                        $game->save();
                    }
                }
            })->daily();
        }
    }
}
