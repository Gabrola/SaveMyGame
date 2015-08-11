<?php

namespace App\Console\Commands;

use LeagueHelper;
use App\Models\Game;
use App\Models\MonitoredUser;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CheckSummoners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:check {batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check monitored users for matches in progress';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batch = $this->argument('batch');

        $monitoredUsersAll = MonitoredUser::whereRaw('id % 3 = ?', [$batch])->whereConfirmed(true)->get()->toArray();

        if(count($monitoredUsersAll) == 0)
            return;

        $monitoredUserChunks = array_chunk($monitoredUsersAll, 2000);

        $client = new Client;

        foreach($monitoredUserChunks as $monitoredUsers) {
            $requests = function ($monitoredUsers) {
                foreach ($monitoredUsers as $user) {
                    yield new Request('GET', 'https://' . LeagueHelper::getApiByRegion($user['region']) . '/observer-mode/rest/consumer/getSpectatorGameInfo/' .
                        LeagueHelper::getPlatformIdByRegion($user['region']) . '/' . $user['summoner_id'] . '?api_key=' . env('RIOT_API_KEY'));
                }
            };

            $pool = new Pool($client, $requests($monitoredUsers), [
                'concurrency' => 2000,
                'fulfilled' => function ($response, $index) {
                    /** @var \GuzzleHttp\Psr7\Response $response */
                    if ($response->getStatusCode() == 200) {
                        $jsonString = $response->getBody();
                        $json = json_decode($jsonString);

                        if (Game::byGame($json->platformId, $json->gameId)->count() > 0)
                            return;

                        try {
                            $game = new Game();
                            $game->platform_id = $json->platformId;
                            $game->game_id = $json->gameId;
                            $game->encryption_key = $json->observers->encryptionKey;
                            $game->start_stats = json_decode($jsonString, true);
                            $game->status = 'not_downloaded';
                            $game->save();

                            $command = $this->getCommand($json->platformId, $json->gameId, $json->observers->encryptionKey);

                            $process = new Process($command, base_path());
                            $process->run();
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
                    }
                },
                'rejected' => function ($reason, $index) {
                    \Log::error($reason);
                },
            ]);

            $chunkStartTime = microtime(true);

            $promise = $pool->promise();
            $promise->wait();

            $chunkTimeElapsed = (microtime(true) - $chunkStartTime) * 1000000;

            if($chunkTimeElapsed < 10000000)
                usleep(10000000 - $chunkTimeElapsed);
        }
    }

    /**
     * @param string $platformId
     * @param integer $gameId
     * @param string $encryptionKey
     * @return string
     */
    protected function getCommand($platformId, $gameId, $encryptionKey)
    {
        $cmd = '%s artisan replay:download %s %d %s';
        $cmd = $this->getBackgroundCommand($cmd);
        $binary = $this->getPhpBinary();
        return sprintf($cmd, $binary, $platformId, $gameId, $encryptionKey);
    }

    /**
     * Get the escaped PHP Binary from the configuration
     *
     * @return string
     */
    protected function getPhpBinary()
    {
        $path = 'php';
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $path = escapeshellarg($path);
        }

        return trim($path);
    }

    protected function getBackgroundCommand($cmd)
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            return 'start /B '.$cmd.' > NUL';
        } else {
            return $cmd.' > /dev/null 2>&1 &';
        }
    }
}
