<?php
namespace App;

use App\Models\ClientVersion;
use App\Models\Game;
use App\Models\Summoner;
use App\Models\SummonerGame;
use File;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use LeagueHelper;

class GameUtil
{
    /**
     * @param Game $game
     */
    public static function DownloadEndGame($game, $retryDownload)
    {
        $handler = HandlerStack::create();
        $middleware = Middleware::retry(function($retries, $request, $response, $e){
            /** @var \Psr\Http\Message\RequestInterface $request */
            if(!is_null($response) || $retries >= 5)
                return false;

            return true;
        }, function($retries){
            return $retries * 250;
        });

        $client = new Client(['handler' => $middleware($handler)]);

        try {
            $requestUrl = 'https://' . LeagueHelper::getApiByPlatformId($game->platform_id) . '/api/lol/' .
                strtolower(LeagueHelper::getRegionByPlatformId($game->platform_id)) . '/v2.2/match/' .
                $game->game_id . '?includeTimeline=true&api_key=' . env('RIOT_API_KEY');

            $res = $client->get($requestUrl);

            if($res->getStatusCode() == 200) {
                $jsonString = $res->getBody();
                $endStats = json_decode($jsonString, true);

                $game->end_stats = $endStats;
                $game->save();
            }
        }
        catch(\Exception $e){}

        self::ProcessEndGame($game, $retryDownload);
    }

    /**
     * @param Game $game
     */
    private static function ProcessEndGame($game, $retryDownload)
    {
        $gameStartStats = $game->start_stats;
        $gameEndStats = $game->end_stats;

        if(is_null($gameStartStats))
            return;

        $region = LeagueHelper::getRegionByPlatformId($game->platform_id);

        foreach($gameStartStats['participants'] as $participantId => $participant)
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

            $summonerGame = SummonerGame::whereRegion($region)->whereSummonerId($summoner->summoner_id)->whereGameId($game->game_id)->first();

            if(is_null($summonerGame)) {
                $summonerGame = new SummonerGame;
                $summonerGame->region = $region;
                $summonerGame->game_id = $game->game_id;
                $summonerGame->summoner_id = $summoner->summoner_id;
            }

            $summonerGame->champion_id = $participant['championId'];
            $summonerGame->spell1 = $participant['spell1Id'];
            $summonerGame->spell2 = $participant['spell2Id'];
            $summonerGame->map_id = $gameStartStats['mapId'];
            $summonerGame->runes = $participant['runes'];
            $summonerGame->masteries = $participant['masteries'];

            if(!is_null($gameEndStats))
            {
                if(isset($gameEndStats['participants'][$participantId]))
                {
                    $endParticipant = $gameEndStats['participants'][$participantId];
                    $summonerGame->stats = $endParticipant['stats'];
                    $summonerGame->win = $endParticipant['stats']['winner'];
                }

                $summonerGame->queue_type = $gameEndStats['queueType'];
                $summonerGame->match_time = round($gameEndStats['matchCreation'] / 1000);
                $summonerGame->match_duration = $gameEndStats['matchDuration'];
            }
            else
            {
                $summonerGame->queue_type = config('constants.queueIdToType.' . (isset($gameStartStats['gameQueueConfigId']) ? $gameStartStats['gameQueueConfigId'] : 0));
                $summonerGame->match_time = round($gameStartStats['gameStartTime'] / 1000);

                if($retryDownload)
                    $summonerGame->stats = false;
            }

            $summoner->games()->save($summonerGame);
        }

        $events = false;

        if($gameEndStats && isset($gameEndStats['timeline']))
        {
            $events = [];
            $killCount = [];
            $lastKill = [];

            foreach($gameEndStats['participants'] as $participant)
            {
                $killCount[$participant['participantId']] = 0;
                $lastKill[$participant['participantId']] = -100000;
            }

            $i = 1;

            foreach($gameEndStats['timeline']['frames'] as $frame)
            {
                if(isset($frame['events'])) {
                    foreach($frame['events'] as $event)
                    {
                        $event['id'] = $i++;

                        if($event['eventType'] == 'CHAMPION_KILL')
                        {
                            $killerId = $event['killerId'];

                            if($killerId > 0) {
                                $lastKillTime = $lastKill[$killerId];
                                $thisKillTime = $event['timestamp'];

                                if ($thisKillTime - $lastKillTime <= 10000) {
                                    $event['multiKill'] = ++$killCount[$killerId];
                                } else {
                                    $event['multiKill'] = $killCount[$killerId] = 1;
                                }

                                $lastKill[$killerId] = $thisKillTime;
                            }
                        }

                        $events[] = $event;
                    }
                }
            }
        }

        $game->events = $events;

        if($gameEndStats && !$retryDownload){
            $currentVersion = config('clientversion', '0.0.0.0');
            $replayVersion = $gameEndStats['matchVersion'];

            $game->patch = LeagueHelper::getPatchFromVersion($replayVersion);

            self::SetReleaseVersion($replayVersion, $game);

            if(version_compare($replayVersion, $currentVersion) > 0) {
                File::put(config_path('clientversion.php'), '<?php return \'' . $replayVersion . '\';');

                if(\App::environment() == 'production')
                    \Artisan::call('config:cache');
            }
        }

        if(is_null($gameEndStats) && $retryDownload) {
            $game->end_stats = false;
        }

        $game->save();
    }

    /**
     * @param string $clientV
     * @param Game $game
     */
    private static function SetReleaseVersion($clientV, $game)
    {
        if(ClientVersion::whereClientVersion($clientV)->count() > 0)
            return;

        $region = LeagueHelper::getRegionByPlatformId($game->platform_id);

        if(!($releaseId = LeagueHelper::getReleaseIdByRegion($region)))
            return;

        $client = new Client;
        $releaseListingUrl = 'http://l3cdn.riotgames.com/releases/live/projects/lol_game_client/releases/releaselisting_' . $releaseId;

        try
        {
            $res = $client->get($releaseListingUrl);
            $releaseListing = $res->getBody();
            $releaseVersion = trim(strtok($releaseListing, "\n"));

            $clientVersion = new ClientVersion;
            $clientVersion->client_version = $clientV;
            $clientVersion->release_version = $releaseVersion;
            $clientVersion->save();
        } catch(\Exception $e){}
    }
}