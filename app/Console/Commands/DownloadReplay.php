<?php

namespace App\Console\Commands;

use App\GameUtil;
use App\Models\ClientVersion;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use LeagueHelper;
use App\Models\Summoner;
use Exception;
use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Chunk;
use App\Models\Keyframe;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use File;

class DownloadReplay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:download {platformId} {gameId} {encryptionKey}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start downloading a replay';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var array
     */
    private $downloadedKeyframes = [];

    /**
     * @var array
     */
    private $downloadedChunks = [];

    /**
     * @var int
     */
    private $lastChunkId = 0;

    /**
     * @var int
     */
    private $lastKeyframeId = 0;

    /**
     * @var int
     */
    private $tabs = 0;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @return bool
     */
    private function GetMetaData()
    {
        try {
            $res = $this->client->get(
                sprintf('getGameMetaData/%s/%d/0/token', $this->game->platform_id, $this->game->game_id)
            );

            if($res->getStatusCode() == 200) {
                $metaData = json_decode($res->getBody());

                $this->game->end_game_chunk_id = 0;
                $this->game->interest_score = $metaData->interestScore;
                $this->game->save();

                return true;
            }
        }
        catch(Exception $e){
            $this->log('GetMetaData Error: %s', $e->getMessage());
        }

        return false;
    }

    /**
     * @return array|bool
     */
    private function GetLastChunkInfo($requestNum)
    {
        try {
            $res = $this->client->get(
                sprintf('getLastChunkInfo/%s/%d/%d/token', $this->game->platform_id, $this->game->game_id, $requestNum)
            );

            if($res->getStatusCode() == 200) {
                return json_decode($res->getBody(), true);
            }
        }
        catch(Exception $e){
            $this->log('GetLastChunkInfo Error: %s', $e->getMessage());
        }

        return false;
    }

    /**
     * @param Chunk $chunk
     */
    private function DownloadChunk($chunk)
    {
        try {
            $res = $this->client->get(
                sprintf('getGameDataChunk/%s/%d/%d/token', $this->game->platform_id, $this->game->game_id, $chunk->chunk_id)
            );

            if($res->getStatusCode() == 200) {
                $chunk->chunk_data = $res->getBody();
                $chunk->save();

                $this->downloadedChunks[] = $chunk->chunk_id;

                return true;
            }
        }
        catch(Exception $e){
            $this->log("DownloadChunk Error: %s", $e->getMessage());
        }

        return false;
    }

    /**
     * @param Keyframe $keyframe
     */
    private function DownloadKeyframe($keyframe)
    {
        try {
            $res = $this->client->get(
                sprintf('getKeyFrame/%s/%d/%d/token', $this->game->platform_id, $this->game->game_id, $keyframe->keyframe_id)
            );

            if($res->getStatusCode() == 200) {
                $keyframe->keyframe_data = $res->getBody();
                $keyframe->save();

                $this->downloadedKeyframes[] = $keyframe->keyframe_id;

                return true;
            }
        }
        catch(Exception $e){
            $this->log("DownloadKeyframe Error: %s", $e->getMessage());
        }

        return false;
    }

    protected function DownloadKeyframes($info)
    {
        $this->log("DownloadKeyframes(lastKeyframeId = %d, info[keyFrameId] = %d)", $this->lastKeyframeId, $info['keyFrameId']);

        $this->tabs++;
        while ($this->lastKeyframeId < $info['keyFrameId'])
        {
            $this->lastKeyframeId++;

            try {
                $keyframe = new Keyframe();
                $keyframe->platform_id = $this->game->platform_id;
                $keyframe->game_id = $this->game->game_id;
                $keyframe->keyframe_id = $this->lastKeyframeId;
                $keyframe->game()->associate($this->game);

                $startTimeKeyframe = round(microtime(true) * 1000);

                if ($this->DownloadKeyframe($keyframe))
                    $this->log("Keyframe %d downloaded in %dms", $this->lastKeyframeId, (round(microtime(true) * 1000) - $startTimeKeyframe));
                else
                    $this->log("Keyframe %d download failed", $this->lastKeyframeId);
            }
            catch(Exception $e) {
                $this->log("DownloadKeyframes(%d) Error: %s", $this->lastKeyframeId, (string)$e);
            }
        }
        $this->tabs--;
    }

    protected function DownloadChunks($info)
    {
        $this->log("DownloadChunks(lastChunkId = %d, info[chunkId] = %d)", $this->lastChunkId, $info['chunkId']);

        $this->tabs++;
        while ($this->lastChunkId < $info['chunkId'])
        {
            $this->lastChunkId++;

            try {
                $chunk = new Chunk();
                $chunk->platform_id = $this->game->platform_id;
                $chunk->game_id = $this->game->game_id;
                $chunk->chunk_id = $this->lastChunkId;
                $chunk->game()->associate($this->game);

                if ($this->lastChunkId == $info['chunkId']) {
                    $chunk->keyframe_id = $info['keyFrameId'];
                    $chunk->next_chunk_id = $info['nextChunkId'];
                    $chunk->duration = $info['duration'];
                } else if ($this->lastChunkId < $info['startGameChunkId'] || $info['startGameChunkId'] == 0) {
                    $chunk->keyframe_id = 0;
                    $chunk->next_chunk_id = 0;
                    $chunk->duration = 0;
                } else {
                    $chunkKeyframeID = floor(($this->lastChunkId - $info['startGameChunkId']) / 2) + 1;
                    $chunk->keyframe_id = $chunkKeyframeID;
                    $chunk->next_chunk_id = $info['startGameChunkId'] + ($chunkKeyframeID - 1) * 2;
                    $chunk->duration = 30000;
                }

                if ($chunk->keyframe_id > 0) {
                    if (!in_array($chunk->keyframe_id, $this->downloadedKeyframes)) {
                        $this->log("Chunk %d skipped because keyframe is not available", $this->lastChunkId);
                        continue;
                    }

                    if ($chunk->chunk_id >= $this->game->start_game_chunk_id &&
                        $chunk->chunk_id != $chunk->next_chunk_id &&
                        !in_array($chunk->next_chunk_id, $this->downloadedChunks)
                    ) {
                        $this->log("Chunk %d skipped because next chunk is not available", $this->lastChunkId);
                        continue;
                    }
                }

                $startTimeChunk = round(microtime(true) * 1000);
                if ($this->DownloadChunk($chunk))
                    $this->log("Chunk %d downloaded in %dms", $this->lastChunkId, (round(microtime(true) * 1000) - $startTimeChunk));
                else
                    $this->log("Chunk %d download failed", $this->lastChunkId);
            }
            catch(Exception $e) {
                $this->log("DownloadChunks(%d) Error: %s", $this->lastChunkId, (string)$e);
            }
        }
        $this->tabs--;
    }

    public function StartDownload()
    {
        $downloaded = false;

        $this->ProcessStartGame();

        if($this->GetMetaData())
        {
            while($this->game->end_game_chunk_id <= 0 || $this->lastChunkId < $this->game->end_game_chunk_id)
            {
                if($this->lastChunkId >= $this->game->start_game_chunk_id)
                    $requestNum = 0;
                else
                    $requestNum = 30000;

                if(!$info = $this->GetLastChunkInfo($requestNum)){
                    $this->log('GetLastChunkInfo failed. Replay download aborted.');

                    $this->game->end_game_chunk_id = $this->lastChunkId;
                    $this->game->save();
                    break;
                }

                $this->log("LastChunkInfo JSON(%s)", json_encode($info));

                $startTime = round(microtime(true) * 1000);

                $this->game->end_startup_chunk_id = $info['endStartupChunkId'];
                $this->game->start_game_chunk_id = $info['startGameChunkId'];
                $this->game->end_game_chunk_id = $info['endGameChunkId'];
                $this->game->save();

                if($this->lastKeyframeId == $info['keyFrameId'] && $this->lastChunkId == $info['chunkId'] && $info['nextAvailableChunk'] == 0 && $info['availableSince'] > 600000) {
                    $this->log("Timeout! No new chunks or keyframes for 10 minutes");
                    break;
                }

                $this->tabs++;
                $this->DownloadKeyframes($info);
                $this->DownloadChunks($info);
                $this->tabs--;

                $executionTime = round(microtime(true) * 1000) - $startTime;

                $sleepTime = min($info['nextAvailableChunk'] - $executionTime + 500, 30000);

                if($info['nextAvailableChunk'] == 0 && $info['availableSince'] > 60000)
                    $sleepTime = 30000;

                if($sleepTime > 0)
                {
                    $this->log("Sleep = %dms", $sleepTime);
                    usleep($sleepTime * 1000);
                }
            }

            $downloaded = true;
        }

        $this->RemoveInGameStatus();
        GameUtil::DownloadEndGame($this->game, false);

        return $downloaded;
    }

    public function ProcessStartGame()
    {
        if(is_null($this->game->start_stats))
            return;

        $region = LeagueHelper::getRegionByPlatformId($this->game->platform_id);

        foreach($this->game->start_stats['participants'] as $participantId => $participant)
        {
            if($participant['bot'])
                continue;

            $summoner = Summoner::bySummonerId($region, $participant['summonerId'])->first();

            if(is_null($summoner))
            {
                $summoner = new Summoner;
                $summoner->region = $region;
                $summoner->summoner_id = $participant['summonerId'];
            }

            $summoner->internal_summoner_name = \LeagueHelper::getInternalName($participant['summonerName']);
            $summoner->summoner_name = $participant['summonerName'];
            $summoner->profile_icon_id = $participant['profileIconId'];
            $summoner->in_game_id = $this->game->id;
            $summoner->touch();
        }
    }

    public function RemoveInGameStatus()
    {
        if(is_null($this->game->start_stats))
            return;

        $region = LeagueHelper::getRegionByPlatformId($this->game->platform_id);

        foreach($this->game->start_stats['participants'] as $participantId => $participant)
        {
            if($participant['bot'])
                continue;

            if($summoner = Summoner::bySummonerId($region, $participant['summonerId'])->first())
            {
                $summoner->in_game_id = 0;
                $summoner->save();
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);

        $platformId = $this->argument('platformId');
        $gameId = $this->argument('gameId');
        $encryptionKey = $this->argument('encryptionKey');

        $handler = HandlerStack::create();
        $middleware = Middleware::retry(function($retries, $request, $response, $e){
            /** @var \Psr\Http\Message\RequestInterface $request */

            //Do not retry if successful request
            if(!is_null($response))
                return false;

            //Do not retry 404 errors
            if($e instanceof ClientException && $e->getResponse()->getStatusCode() == 404)
                return false;

            //Do not retry after 5 retries
            if($retries >= 5)
                return false;

            $this->log('Request Retried: %s', $request->getUri());

            return true;
        }, function($retries){
            return $retries * 250;
        });

        $this->client = new \GuzzleHttp\Client([
            'base_uri'  => 'http://' . \LeagueHelper::getDomainByPlatformId($platformId) . '/observer-mode/rest/consumer/',
            'handler'   => $middleware($handler)
        ]);

        $this->game = Game::firstOrNew([
            'platform_id'   => $platformId,
            'game_id'       => $gameId
        ]);

        $gameString = $platformId . '-' . $gameId;

        $this->logger = new Logger('Replay');
        $logFile = storage_path('logs/replays/' . $gameString . '.log');

        $handler = new StreamHandler($logFile, Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $this->logger->pushHandler($handler);

        $this->game->encryption_key = $encryptionKey;
        $this->game->status = 'downloading';
        $this->game->save();

        if($this->StartDownload()) {
            $this->log("Game %s-%d downloaded successfully!", $this->game->platform_id, $this->game->game_id);
            $this->game->status = 'downloaded';
            $this->game->save();

            File::delete($logFile);
        } else {
            $this->log("Game %s-%d download failed!", $this->game->platform_id, $this->game->game_id);
            $this->game->status = 'failed';
            $this->game->save();
        }
    }

    public function log($string)
    {
        $args = func_get_args();
        $string = str_repeat("\t", $this->tabs) . array_shift($args);

        try {
            $this->logger->info(vsprintf($string, $args));
        }
        catch(\Exception $e) {
            \Log::error($e);
        }
    }
}