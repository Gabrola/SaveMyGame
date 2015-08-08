<?php

namespace App\Console\Commands;

use App\Models\ClientVersion;
use GuzzleHttp\Exception\ClientException;
use LeagueHelper;
use App\Models\Summoner;
use App\Models\SummonerGame;
use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Chunk;
use App\Models\Keyframe;

class DownloadReplay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:download {platformId} {gameId} {encryptionKey} {updateSummoner=y}';

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
     * @var bool
     */
    private $endGameStatsFailed = false;

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

                $this->game->interest_score = $metaData->interestScore;
                $this->game->save();

                return true;
            }
        }
        catch(\Exception $e){}

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
        catch(\Exception $e){}

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

                return true;
            }
        }
        catch(\Exception $e){}

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

                return true;
            }
        }
        catch(\Exception $e){}

        return false;
    }

    private function GetEndOfGameStats()
    {
        try {
            /*$res = $this->client->get(
                sprintf('endOfGameStats/%s/%d/null', $this->game->platform_id, $this->game->game_id)
            );

            if($res->getStatusCode() == 200) {
                return gzencode($res->getBody());
            }*/

            $requestUrl = 'https://' . LeagueHelper::getApiByPlatformId($this->game->platform_id) . '/api/lol/' .
                strtolower(LeagueHelper::getRegionByPlatformId($this->game->platform_id)) . '/v2.2/match/' .
                $this->game->game_id . '?api_key=' . env('RIOT_API_KEY');

            $res = $this->client->get($requestUrl);

            if($res->getStatusCode() == 200) {
                return json_decode($res->getBody(), true);
            }
        }
        catch (ClientException $e) {
            if($e->getResponse()->getStatusCode() == 429)
                $this->endGameStatsFailed = true;
        }
        catch(\Exception $e){}

        return false;
    }

    public function StartDownload()
    {
        $chunkID = 0;
        $keyframeID = 0;
        $endChunk = 0;
        $downloaded = false;

        $this->ProcessStartGame();

        if($this->GetMetaData())
        {
            while($endChunk <= 0 || $chunkID < $endChunk)
            {
                if($chunkID >= $this->game->start_game_chunk_id)
                    $requestNum = 0;
                else
                    $requestNum = 30000;

                if(!$info = $this->GetLastChunkInfo($requestNum)){
                    $endChunk = $chunkID;
                    $this->game->end_game_chunk_id = $endChunk;
                    $this->game->save();
                    break;
                }

                $this->comment('');
                $this->comment("Chunk Info (Chunk ID = " . $info['chunkId'] . ", Keyframe ID = " . $info['keyFrameId'] . ")");

                $startTime = round(microtime(true) * 1000);

                $this->game->end_startup_chunk_id = $info['endStartupChunkId'];
                $this->game->start_game_chunk_id = $info['startGameChunkId'];
                $this->game->end_game_chunk_id = $info['endGameChunkId'];
                $this->game->save();

                $startKeyframeID = $keyframeID;
                $startChunkID = $chunkID;

                for($i = 1; $startKeyframeID + $i <= $info['keyFrameId'] || $startChunkID + $i <= $info['chunkId']; $i++)
                {
                    $currentKeyframeID = $startKeyframeID + $i;
                    $currentChunkID = $startChunkID + $i;

                    if($currentKeyframeID <= $info['keyFrameId']) {
                        $keyframe = new Keyframe();
                        $keyframe->platform_id = $this->game->platform_id;
                        $keyframe->game_id = $this->game->game_id;
                        $keyframe->keyframe_id = $currentKeyframeID;
                        $keyframe->game()->associate($this->game);

                        $startTimeKeyframe = round(microtime(true) * 1000);
                        if ($this->DownloadKeyframe($keyframe)) {
                            $keyframeID = $currentKeyframeID;
                            $this->downloadedKeyframes[] = $currentKeyframeID;

                            $this->comment("Keyframe $currentKeyframeID downloaded in " . (round(microtime(true) * 1000) - $startTimeKeyframe) . "ms");
                        } else
                            $this->comment("Keyframe $currentKeyframeID download failed");
                    }

                    if($currentChunkID <= $info['chunkId']){
                        $chunk = new Chunk();
                        $chunk->platform_id = $this->game->platform_id;
                        $chunk->game_id = $this->game->game_id;
                        $chunk->chunk_id = $currentChunkID;
                        $chunk->game()->associate($this->game);

                        if($currentChunkID == $info['chunkId']) {
                            $chunk->keyframe_id = $info['keyFrameId'];
                            $chunk->next_chunk_id = $info['nextChunkId'];
                            $chunk->duration = $info['duration'];
                        } else if ($currentChunkID < $info['startGameChunkId']) {
                            $chunk->keyframe_id = 0;
                            $chunk->next_chunk_id = 0;
                            $chunk->duration = 0;
                        } else {
                            $chunkKeyframeID = floor(($currentChunkID - $info['startGameChunkId']) / 2) + 1;
                            $chunk->keyframe_id = $chunkKeyframeID;
                            $chunk->next_chunk_id = $info['startGameChunkId'] + ($chunkKeyframeID - 1) * 2;
                            $chunk->duration = 30000;
                        }

                        if(!in_array($chunk->keyframe_id, $this->downloadedKeyframes) && $chunk->keyframe_id != 0) {
                            $this->comment("Chunk $currentChunkID skipped because keyframe is not available");
                            continue;
                        }

                        if($chunk->chunk_id >= $this->game->start_game_chunk_id &&
                            $chunk->chunk_id != $chunk->next_chunk_id &&
                            !in_array($chunk->next_chunk_id, $this->downloadedChunks)){
                            $this->comment("Chunk $currentChunkID skipped because next chunk is not available");
                            continue;
                        }

                        $startTimeChunk = round(microtime(true) * 1000);
                        if($this->DownloadChunk($chunk)) {
                            $chunkID = $currentChunkID;
                            $this->downloadedChunks[] = $currentChunkID;

                            $this->comment("Chunk $currentChunkID downloaded in " . (round(microtime(true) * 1000) - $startTimeChunk) . "ms");
                        } else
                            $this->comment("Chunk $currentChunkID download failed");
                    }
                }

                if($info['endGameChunkId'] > 0)
                    $endChunk = $info['endGameChunkId'];

                $executionTime = round(microtime(true) * 1000) - $startTime;

                $sleepTime = min($info['nextAvailableChunk'] - $executionTime + 500, 10000);

                if($sleepTime > 0)
                {
                    $this->comment("Sleep = " . $sleepTime . "ms");
                    usleep($sleepTime * 1000);
                }
            }

            $downloaded = true;
        }

        $this->RemoveInGameStatus();

        if($endGameStats = $this->GetEndOfGameStats()){
            $this->game->end_stats = $endGameStats;
            $this->game->save();
            return true;
        }

        return $downloaded;
    }

    public function ProcessStartGame()
    {
        if(is_null($this->game->start_stats))
            return;

        $region = LeagueHelper::getRegionByPlatformId($this->game->platform_id);

        $updateSummoner = strtolower($this->argument('updateSummoner'));

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

            if($updateSummoner == 'y') {
                $summoner->internal_summoner_name = \LeagueHelper::getInternalName($participant['summonerName']);
                $summoner->summoner_name = $participant['summonerName'];
                $summoner->profile_icon_id = $participant['profileIconId'];
                $summoner->in_game_id = $this->game->id;
                $summoner->touch();
            } else {
                $summoner->save();
            }
        }
    }

    public function RemoveInGameStatus()
    {
        $updateSummoner = strtolower($this->argument('updateSummoner'));

        if(is_null($this->game->start_stats) || $updateSummoner != 'y')
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

    public function ProcessEndGame()
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
                $summoner->internal_summoner_name = \LeagueHelper::getInternalName($participant['summonerName']);
                $summoner->summoner_name = $participant['summonerName'];
                $summoner->profile_icon_id = $participant['profileIconId'];
                $summoner->save();
            }

            $summonerGame = SummonerGame::whereRegion($region)->whereSummonerId($summoner->summoner_id)->whereGameId($this->game->game_id)->first();

            if(is_null($summonerGame)) {
                $summonerGame = new SummonerGame;
                $summonerGame->region = $region;
                $summonerGame->game_id = $this->game->game_id;
                $summonerGame->summoner_id = $summoner->summoner_id;
            }

            $summonerGame->champion_id = $participant['championId'];
            $summonerGame->spell1 = $participant['spell1Id'];
            $summonerGame->spell2 = $participant['spell2Id'];
            $summonerGame->map_id = $this->game->start_stats['mapId'];
            $summonerGame->runes = $participant['runes'];
            $summonerGame->masteries = $participant['masteries'];

            if(!is_null($this->game->end_stats))
            {
                $endParticipant = $this->game->end_stats['participants'][$participantId];
                $summonerGame->queue_type = $this->game->end_stats['queueType'];
                $summonerGame->match_time = round($this->game->end_stats['matchCreation'] / 1000);
                $summonerGame->match_duration = $this->game->end_stats['matchDuration'];
                $summonerGame->stats = $endParticipant['stats'];
                $summonerGame->win = $endParticipant['stats']['winner'];
            }
            else
            {
                $summonerGame->queue_type = config('constants.queueIdToType.' . (isset($this->game->start_stats['gameQueueConfigId']) ? $this->game->start_stats['gameQueueConfigId'] : 0));
                $summonerGame->match_time = round($this->game->start_stats['gameStartTime'] / 1000);

                if($this->game->end_game_chunk_id > 0 && !$this->endGameStatsFailed)
                {
                    $summonerGame->stats = false;
                }
            }

            $summoner->games()->save($summonerGame);
        }

        if($this->game->end_stats){
            $currentVersion = config('clientversion', '0.0.0.0');
            $replayVersion = $this->game->end_stats['matchVersion'];

            $this->SetReleaseVersion($replayVersion);

            if(version_compare($replayVersion, $currentVersion) > 0)
                \File::put(config_path('clientversion.php'), '<?php return \'' . $replayVersion . '\';');
        }

        if(is_null($this->game->end_stats) && $this->game->end_game_chunk_id > 0 && !$this->endGameStatsFailed) {
            $this->game->end_stats = false;
            $this->game->save();
        }
    }

    public function SetReleaseVersion($clientV)
    {
        if(ClientVersion::whereClientVersion($clientV)->count() > 0)
            return;

        $region = LeagueHelper::getRegionByPlatformId($this->game->platform_id);

        if(!($releaseId = LeagueHelper::getReleaseIdByRegion($region)))
            return;

        $releaseListingUrl = 'http://l3cdn.riotgames.com/releases/live/projects/lol_game_client/releases/releaselisting_' . $releaseId;

        try
        {
            $res = $this->client->get($releaseListingUrl);
            $releaseListing = $res->getBody();
            $releaseVersion = trim(strtok($releaseListing, "\n"));

            $clientVersion = new ClientVersion;
            $clientVersion->client_version = $clientV;
            $clientVersion->release_version = $releaseVersion;
            $clientVersion->save();
        } catch(\Exception $e){}
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $platformId = $this->argument('platformId');
        $gameId = $this->argument('gameId');
        $encryptionKey = $this->argument('encryptionKey');
        $updateSummoner = strtolower($this->argument('updateSummoner'));

        $this->client = new \GuzzleHttp\Client([
            'base_uri'  => 'http://' . \LeagueHelper::getDomainByPlatformId($platformId) . '/observer-mode/rest/consumer/'
        ]);

        $this->game = Game::firstOrNew([
            'platform_id'   => $platformId,
            'game_id'       => $gameId
        ]);

        $this->game->encryption_key = $encryptionKey;
        $this->game->status = 'downloading';
        $this->game->save();

        if($this->StartDownload()) {
            $this->comment("Game " . $this->game->platform_id . "-" . $this->game->game_id . " downloaded successfully!");
            $this->game->status = 'downloaded';
            $this->game->save();
            $this->ProcessEndGame();
        } else if($updateSummoner == 'y') {
            $this->comment("Game " . $this->game->platform_id . "-" . $this->game->game_id . " download failed!");
            $this->game->status = 'failed';
            $this->game->save();
        }
    }
}