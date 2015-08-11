<?php

namespace App\Http\Controllers;

use App\Models\ClientVersion;
use App\Models\MonitoredUser;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
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
        try {
            $monitoredUsers = MonitoredUser::whereConfirmed(true)->limit(100)->get();

            // Initiate each request but do not block
            $requests = [];

            $client = new Client;

            $startTime = microtime(true);

            $output = '';

            /** @var MonitoredUser $user */
            foreach ($monitoredUsers as $user)
                $requests[] = new \GuzzleHttp\Psr7\Request('GET', 'https://' . \LeagueHelper::getApiByRegion($user->region) . '/observer-mode/rest/consumer/getSpectatorGameInfo/' .
                    \LeagueHelper::getPlatformIdByRegion($user->region) . '/' . $user->summoner_id . '?api_key=' . env('RIOT_API_KEY'));

            $pool = new Pool($client, $requests, [
                'concurrency' => 100,
                'fulfilled' => function ($response, $index) use(&$output, $startTime) {
                    $output .= (microtime(true) - $startTime) . '<br>';
                },
                'rejected' => function ($reason, $index) use(&$output, $startTime) {
                    $output .= (microtime(true) - $startTime) . '<br>';
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();

            $commandTime = microtime(true) - $startTime;

            return $output . 'CheckSummoners Time = ' . $commandTime . ' seconds';
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
