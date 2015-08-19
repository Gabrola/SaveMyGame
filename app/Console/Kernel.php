<?php

namespace App\Console;

use App\Models\Game;
use App\Models\MonitoredUser;
use Artisan;
use Carbon\Carbon;
use GuzzleHttp\Client;
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
        \App\Console\Commands\CleanMatches::class,
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
        $schedule->command('replay:check 0')->cron('0/3 * * * *');
        $schedule->command('replay:check 1')->cron('1/3 * * * *');
        $schedule->command('replay:check 2')->cron('2/3 * * * *');
        $schedule->command('replay:static')->cron('0 */6 * * *');

        $schedule->call(function(){
            $hourAgo = Carbon::now()->subHour()->toDateTimeString();
            MonitoredUser::whereConfirmed(false)->where('created_at', '<=', $hourAgo)->delete();
        })->everyFiveMinutes();

        $schedule->call(function(){
            $unconfirmedMonitoredUsers = MonitoredUser::whereConfirmed(false)->get();

            $client = new Client;

            /** @var \App\Models\MonitoredUser $monitoredUser */
            foreach($unconfirmedMonitoredUsers as $monitoredUser) {
                $requestUrl = 'https://' . LeagueHelper::getApiByRegion($monitoredUser->region) . '/api/lol/' .
                    strtolower($monitoredUser->region) . '/v1.4/summoner/' .
                    $monitoredUser->summoner_id . '/runes?api_key=' . env('RIOT_API_KEY');

                try {
                    $res = $client->get($requestUrl);
                    $json = json_decode($res->getBody(), true);

                    $summonerId = strval($monitoredUser->summoner_id);

                    if(array_key_exists($summonerId, $json)) {
                        $pages = $json[$summonerId]['pages'];

                        foreach($pages as $runePage){
                            if(str_contains($runePage['name'], $monitoredUser->confirmation_code)){
                                $monitoredUser->confirmed = true;
                                $monitoredUser->save();
                                break;
                            }
                        }
                    }
                } catch (\Exception $e){}
            }
        })->everyMinute();

        if(app()->environment() == 'production')
        {
            $schedule->call(function () {
                $sevenDaysAgo = Carbon::now()->subDays(7)->toDateTimeString();
                $games = Game::where('created_at', '<', $sevenDaysAgo)->get();

                /** @var \App\Models\Game $game */
                foreach($games as $game)
                {
                    $gameEndStats = $game->end_stats;
                    if (!$gameEndStats || LeagueHelper::comparePatch(config('clientversion', '0.0.0.0'), $gameEndStats['matchVersion']))
                        $game->deleteReplay();
                }
            })->daily();
        }
    }
}
