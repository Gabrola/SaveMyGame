<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Database\QueryException;
use LeagueHelper;
use App\Models\Game;
use App\Models\MonitoredUser;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
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

        $handler = HandlerStack::create();
        $middleware = Middleware::retry(function($retries, $request, $response, $e){
            /** @var \Psr\Http\Message\RequestInterface $request */
            if(!is_null($response))
                return false;

            if(!($e instanceof ClientException) && $retries < 5) {
                return true;
            }

            return false;
        }, function($retries){
            return $retries * 250;
        });

        $client = new Client(['handler' => $middleware($handler)]);

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
                    $this->handleResponse($response);
                },
                'rejected' => function ($reason, $index) {
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
     * @param \GuzzleHttp\Psr7\Response $response
     */
    protected function handleResponse($response)
    {
        if ($response->getStatusCode() == 200) {
            $json = json_decode($response->getBody(), true);

            if($json === NULL)
                return;

            if (Game::byGame($json['platformId'], $json['gameId'])->count() > 0)
                return;

            try {
                $game = new Game();
                $game->platform_id = $json['platformId'];
                $game->game_id = $json['gameId'];
                $game->encryption_key = $json['observers']['encryptionKey'];
                $game->start_stats = $json;
                $game->status = 'not_downloaded';
                $game->patch = LeagueHelper::getPatchFromVersion(config('clientversion', '0.0.0.0'));
                $game->save();

                $command = $this->getCommand($json['platformId'], $json['gameId'], $json['observers']['encryptionKey']);

                $process = new Process($command, base_path());
                $process->setTimeout(null);
                $process->run();
            }
            catch (\Exception $e) {
                if(!($e instanceof QueryException))
                    \Log::error($e);
            }
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
