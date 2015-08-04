<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\MonitoredUser;
use App\Models\Summoner;
use App\Models\SummonerGame;
use App\SummonerSearch;
use Illuminate\Http\Request;
use App\Http\Requests;

class SummonerController extends Controller
{
    public function postSearch(Request $request)
    {
        if(!$request->has(['region', 'summoner_name']))
            return response()->redirectTo('/');

        $region = $request->input('region');
        $summonerName = $request->input('summoner_name');

        if(!\LeagueHelper::regionExists($region))
            abort(404);

        if($summoner = SummonerSearch::ByName($region, $summonerName))
        {
            return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_name])
                ->withCookie(cookie()->forever('search_region', $summoner->region));
        }

        return view('errors/summoner-404');
    }

    public function getIndex(Request $request, $region, $summonerName)
    {
        if(!\LeagueHelper::regionExists($region))
            abort(404);

        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerName($region, $summonerName)->first();

        if(is_null($summoner)) {
            if($request->ajax())
                abort(404);
            else
                return view('errors/summoner-404');
        }

        $summonerGames = $summoner->games()->orderBy('id', 'desc')->paginate(7);
        $monitoredUserExists = MonitoredUser::bySummonerId($region, $summoner->summoner_id)->exists();

        $somethingChanged = false;

        /** @var \App\Models\SummonerGame $summonerGame */
        foreach($summonerGames as $summonerGame){
            if(is_null($summonerGame->stats)){
                /** @var \App\Models\Game $game */
                $game = Game::byGame(\LeagueHelper::getPlatformIdByRegion($region), $summonerGame->game_id)->first();

                \Artisan::call('replay:download', [
                    'platformId'    => \LeagueHelper::getPlatformIdByRegion($summonerGame->region),
                    'gameId'        => $summonerGame->game_id,
                    'encryptionKey' => $game->encryption_key,
                    'updateSummoner'=> 'n'
                ]);

                $summonerGame = SummonerGame::find($summonerGame->id);

                if(is_null($summonerGame->stats)){
                    $summonerGame->stats = false;
                    $summonerGame->save();
                } else {
                    $somethingChanged = true;
                }
            }
        }

        if($somethingChanged){
            return $this->getIndex($request, $region, $summonerName);
        }

        if($request->ajax()){
            return view('_games', [
                'summoner' => $summoner,
                'games' => $summonerGames
            ]);
        } else {
            return view('summoner', [
                'summoner' => $summoner,
                'games' => $summonerGames,
                'is_monitored' => $monitoredUserExists
            ]);
        }
    }

    public function getById(Request $request, $region, $summonerId)
    {
        if(!\LeagueHelper::regionExists($region))
            abort(404);

        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerId($region, $summonerId)->first();

        if(is_null($summoner))
            return view('errors/summoner-404');

        return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_name]);
    }

    public function getGame(Request $request, $region, $gameId)
    {
        if(!\LeagueHelper::regionExists($region))
            abort(404);

        /** @var \App\Models\Game $game */
        $game = Game::byGame(\LeagueHelper::getPlatformIdByRegion($region), $gameId)->first();

        if(is_null($game))
            return view('errors/game-404');

        if(is_null($game->end_stats))
        {
            \Artisan::call('replay:download', [
                'platformId'    => $game->platform_id,
                'gameId'        => $game->game_id,
                'encryptionKey' => $game->encryption_key,
                'updateSummoner'=> 'n'
            ]);

            /** @var \App\Models\Game $game */
            $game = Game::byGame(\LeagueHelper::getPlatformIdByRegion($region), $gameId)->first();

            if(is_null($game->end_stats))
            {
                $game->end_stats = false;
                $game->save();
            }
        }

        return view('game', [
            'game' => $game
        ]);
    }

    public function getRecord(Request $request, $region, $summonerId)
    {
        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerId($region, $summonerId)->first();

        if(is_null($summoner)) {
            if($request->ajax())
                abort(404);
            else
                return view('errors/summoner-404');
        }

        $monitoredUser = MonitoredUser::bySummonerId($region, $summonerId)->first();

        if(is_null($monitoredUser)) {
            $monitoredUser = new MonitoredUser();
            $monitoredUser->region = $summoner->region;
            $monitoredUser->summoner_id = $summoner->summoner_id;
            $monitoredUser->save();
        }

        if($request->ajax()){
            return response()->json(true);
        } else {
            return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_id])
                ->with('message', 'Summoner games will be recorded automatically!')
                ->with('message-color', 'green');
        }
    }
}
