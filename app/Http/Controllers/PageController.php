<?php

namespace App\Http\Controllers;

use App\Models\ClientVersion;
use App\Models\MonitoredUser;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $defaultRegion = key(\LeagueHelper::getAllServerNames());

        if($request->hasCookie('search_region')){
            $regionCookie = $request->cookie('search_region');

            if(\LeagueHelper::regionExists($regionCookie))
                $defaultRegion = $regionCookie;
        }

        return view('index', [
            'defaultRegion'    => $defaultRegion
        ]);
    }

    public function faq()
    {
        return view('faq');
    }

    public function replay($region, $matchId)
    {
        if(!$platformId = \LeagueHelper::getPlatformIdByRegion($region))
            abort(404);

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::byGame($platformId, $matchId)->first();

        if(is_null($game))
            abort(404);

        $batFile = sprintf(config('constants.batfile'), env('APP_DOMAIN', 'localhost'), $game->encryption_key, $game->game_id, $game->platform_id);

        return response()->make($batFile, '200', array(
            'Content-Type' => 'application/x-bat',
            'Content-Disposition' => 'attachment; filename="REPLAY_' . $game->platform_id . $game->game_id . '.bat"'
        ));
    }

    public function replayAlt($region, $matchId)
    {
        if(!$platformId = \LeagueHelper::getPlatformIdByRegion($region))
            abort(404);

        /** @var \App\Models\Game $game */
        $game = \App\Models\Game::byGame($platformId, $matchId)->first();

        if(is_null($game))
            abort(404);

        $batFile = sprintf(config('constants.batfileAlt'), env('APP_DOMAIN', 'localhost'), $game->encryption_key, $game->game_id, $game->platform_id);

        return response()->make($batFile, '200', array(
            'Content-Type' => 'application/x-bat',
            'Content-Disposition' => 'attachment; filename="REPLAY_' . $game->platform_id . $game->game_id . '.bat"'
        ));
    }

    public function versions()
    {
        $clientVersions = ClientVersion::all();
        $listing = [];

        /** @var \App\Models\ClientVersion $clientVersion */
        foreach($clientVersions as $clientVersion)
            $listing[] = $clientVersion->client_version . ':' . $clientVersion->release_version;

        $output = implode("\n", $listing);

        return response()->make($output, 200, [
            'Content-Type' => 'text/plain'
        ]);
    }

    public function test()
    {
        $monitoredUsers = MonitoredUser::whereConfirmed(true)->whereRegion('OCE')->get();

        // Initiate each request but do not block
        $promises = [];

        $client = new Client;

        $startTime = microtime(true);

        /** @var MonitoredUser $monitoredUser */
        foreach($monitoredUsers as $monitoredUser)
            $promises[$monitoredUser->id] = $client->getAsync('https://' . \LeagueHelper::getApiByRegion($monitoredUser->region));

        $results = Promise\unwrap($promises);

        $commandTime = microtime(true) - $startTime;

        return 'CheckSummoners Time = ' . $commandTime . ' seconds';
    }
}
