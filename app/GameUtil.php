<?php
namespace App;

use App\Models\ClientVersion;
use App\Models\Game;
use App\Models\Summoner;
use App\Models\SummonerGame;
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
                $endStats = json_decode($res->getBody(), true);

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
        if(is_null($game->start_stats))
            return;

        $region = LeagueHelper::getRegionByPlatformId($game->platform_id);

        foreach($game->start_stats['participants'] as $participantId => $participant)
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
            $summonerGame->map_id = $game->start_stats['mapId'];
            $summonerGame->runes = $participant['runes'];
            $summonerGame->masteries = $participant['masteries'];

            if(!is_null($game->end_stats))
            {
                if(isset($game->end_stats['participants'][$participantId]))
                {
                    $endParticipant = $game->end_stats['participants'][$participantId];
                    $summonerGame->stats = $endParticipant['stats'];
                    $summonerGame->win = $endParticipant['stats']['winner'];
                }

                $summonerGame->queue_type = $game->end_stats['queueType'];
                $summonerGame->match_time = round($game->end_stats['matchCreation'] / 1000);
                $summonerGame->match_duration = $game->end_stats['matchDuration'];
            }
            else
            {
                $summonerGame->queue_type = config('constants.queueIdToType.' . (isset($game->start_stats['gameQueueConfigId']) ? $game->start_stats['gameQueueConfigId'] : 0));
                $summonerGame->match_time = round($game->start_stats['gameStartTime'] / 1000);

                if($retryDownload)
                    $summonerGame->stats = false;
            }

            $summoner->games()->save($summonerGame);
        }

        $events = false;

        if($game->end_stats && isset($game->end_stats['timeline']))
        {
            $events = [];
            $killCount = [];
            $lastKill = [];

            foreach($game->end_stats['participants'] as $participant)
            {
                $killCount[$participant['participantId']] = 0;
                $lastKill[$participant['participantId']] = -100000;
            }

            $i = 1;

            foreach($game->end_stats['timeline']['frames'] as $frame)
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

        if($game->end_stats && !$retryDownload){
            $currentVersion = config('clientversion', '0.0.0.0');
            $replayVersion = $game->end_stats['matchVersion'];

            self::SetReleaseVersion($replayVersion, $game);

            if(version_compare($replayVersion, $currentVersion) > 0)
                \File::put(config_path('clientversion.php'), '<?php return \'' . $replayVersion . '\';');
        }

        if(is_null($game->end_stats) && $retryDownload) {
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