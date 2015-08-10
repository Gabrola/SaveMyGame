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

    public function multiRequest($data, $options = array()) {

        // array of curl handles
        $curly = array();
        // data to be returned
        $result = array();

        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();

            $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
            curl_setopt($curly[$id], CURLOPT_URL,            $url);
            curl_setopt($curly[$id], CURLOPT_HEADER,         0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curly[$id], CURLOPT_SSL_VERIFYPEER, false);

            // post?
            if (is_array($d)) {
                if (!empty($d['post'])) {
                    curl_setopt($curly[$id], CURLOPT_POST,       1);
                    curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
                }
            }

            // extra options?
            if (!empty($options)) {
                curl_setopt_array($curly[$id], $options);
            }

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while($running > 0);


        // get content and remove handles
        foreach($curly as $id => $c) {
            $result[$id] = curl_multi_getcontent($c);
            curl_multi_remove_handle($mh, $c);
        }

        // all done
        curl_multi_close($mh);

        return $result;
    }

    public function test()
    {
        try {
            $monitoredUsers = MonitoredUser::whereConfirmed(true)->whereRegion('OCE')->limit(10)->get();

            // Initiate each request but do not block
            $data = [];

            /** @var MonitoredUser $monitoredUser */
            foreach ($monitoredUsers as $monitoredUser)
                $data[] = 'https://oce.api.pvp.net/observer-mode/rest/consumer/getSpectatorGameInfo/OC1/' . $monitoredUser->summoner_id . '?api_key=' . env('RIOT_API_KEY');

            $startTime = microtime(true);

            $this->multiRequest($data);

            $commandTime = microtime(true) - $startTime;

            return 'CheckSummoners Time = ' . $commandTime . ' seconds';
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
