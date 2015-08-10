<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\ClientException;
use \LeagueHelper;
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

        $monitoredUsers = MonitoredUser::whereRaw('id % 3 = ?', [$batch])->whereConfirmed(true)->get();

        if($monitoredUsers->count() == 0)
            return;

        $loopRunTimes = 0;

        $client = new Client();

        /** @var \App\Models\MonitoredUser $user */
        foreach($monitoredUsers as $user){
            $loopRunTimes++;
            $requestUrl = 'https://' . LeagueHelper::getApiByRegion($user->region) . '/observer-mode/rest/consumer/getSpectatorGameInfo/' .
                LeagueHelper::getPlatformIdByRegion($user->region) . '/' . $user->summoner_id . '?api_key=' . env('RIOT_API_KEY');

            try {
                $response = $client->get($requestUrl);

                if($response->getStatusCode() == 200){
                    $jsonString = $response->getBody();
                    $json = json_decode($jsonString);

                    if(Game::byGame($json->platformId, $json->gameId)->count() > 0)
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
                    }
                    catch(\Exception $e) {
                        \Log::error($requestUrl . ' ' . $e->getMessage());
                    }
                } else {
                    \Log::error($requestUrl . ' ' . $response->getStatusCode());
                }
            } catch(ClientException $e){
                \Log::error($requestUrl . ' ' . $e->getMessage());
            }
            catch(\Exception $e){
            }

            usleep(10 * 1000);
        }

        \Log::error('Loop ran ' . $loopRunTimes . ' times and array count is ' . $monitoredUsers->count());
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
