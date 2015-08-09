<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\MonitoredUser;
use App\Models\Summoner;
use App\Models\SummonerGame;
use App\SummonerSearch;
use Illuminate\Http\Request;
use App\Http\Requests;
use Mail;
use Session;

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

        return response()->redirectToAction('SummonerController@getIndex', [$region, $summonerName])
            ->withCookie(cookie()->forever('search_region', $region));
    }

    public function getIndex(Request $request, $region, $summonerName)
    {
        if(!\LeagueHelper::regionExists($region))
            abort(404);

        /** @var \App\Models\Summoner $summoner */
        if(!($summoner = SummonerSearch::ByName($region, $summonerName))) {
            if ($request->ajax())
                abort(404);
            else
                return redirect()->route('index')->withErrors('Summoner does not exist.');
        }

        $summonerGames = $summoner->games()->orderBy('id', 'desc')->paginate(7);

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

        $isMonitored = false;

        /** @var \App\Models\MonitoredUser $monitoredUser */
        if($monitoredUser = MonitoredUser::bySummonerId($region, $summoner->summoner_id)->first())
            $isMonitored = $monitoredUser->confirmed;

        $inGame = $summoner->in_game_id != 0;

        /** @var \App\Models\Game $inGameData */
        $inGameData = $inGame ? Game::find($summoner->in_game_id) : null;

        if($inGameData && $inGameData->status != 'downloading')
            $inGame = false;

        if($request->ajax()){
            return view('_games', [
                'summoner' => $summoner,
                'games' => $summonerGames
            ]);
        } else {
            return view('summoner', [
                'summoner'      => $summoner,
                'games'         => $summonerGames,
                'isMonitored'   => $isMonitored,
                'monitoredUser' => $monitoredUser,
                'inGame'        => $inGame,
                'inGameData'    => $inGameData
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
            return redirect()->route('index')->withErrors('Summoner does not exist.');

        return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_name]);
    }

    public function getGame(Request $request, $region, $gameId)
    {
        if(!\LeagueHelper::regionExists($region))
            abort(404);

        /** @var \App\Models\Game $game */
        $game = Game::byGame(\LeagueHelper::getPlatformIdByRegion($region), $gameId)->first();

        if(is_null($game))
            return redirect()->route('index')->withErrors('Game not found.');

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
        }

        $command = sprintf('replay %s:80 %s %s %s', env('APP_DOMAIN', 'localhost'), $game->encryption_key, $game->game_id, $game->platform_id);
        $binaryData = pack('VVVVA*', 16, 1, 0, strlen($command), $command);
        $binaryArray = implode(',', unpack('C*', $binaryData));
        $hexString = preg_replace_callback("/../", function($matched) {
            return '\x' . $matched[0];
        }, bin2hex($binaryData));

        $windowsCommand = sprintf(config('constants.windowsCommand'), $binaryArray);
        $macCommand = sprintf(config('constants.macCommand'), $hexString);

        return view('game', [
            'game'              => $game,
            'windowsCommand'    => $windowsCommand,
            'macCommand'        => $macCommand
        ]);
    }

    public function postRecord(Request $request)
    {
        $this->validate($request, [
            'region'                    => 'required|region',
            'summoner_id'               => 'required|integer',
            'g-recaptcha-response'      => 'required|recaptcha'
        ]);

        $region = $request->input('region');
        $summonerId = $request->input('summoner_id');

        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerId($region, $summonerId)->first();

        if(is_null($summoner)) {
            if($request->ajax())
                abort(404);
            else
                return redirect()->route('index')->withErrors('Summoner does not exist.');
        }

        $monitoredUser = MonitoredUser::bySummonerId($region, $summonerId)->first();

        if(!is_null($monitoredUser)){
            if($request->ajax())
                return response()->json([
                    'summoner'  => [ 'Summoner is already monitored' ]
                ], 422);
            else
                return response()
                    ->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_id])
                    ->withErrors('Summoner is already monitored');
        }

        $monitoredUser = new MonitoredUser();
        $monitoredUser->region = $summoner->region;
        $monitoredUser->summoner_id = $summoner->summoner_id;
        $monitoredUser->confirmation_code = str_random(10);
        $monitoredUser->save();

        if($request->ajax()){
            return response()->json([ 'code' => $monitoredUser->confirmation_code ]);
        } else {
            Session::flash('message', 'Please change one of your rune page names to <strong>'.$monitoredUser->confirmation_code.'</strong>.');
            Session::flash('message_color', 'green');

            return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_id]);
        }
    }

    public function verify($confirmationCode)
    {
        /** @var \App\Models\MonitoredUser $monitoredUser */
        $monitoredUser = MonitoredUser::whereConfirmationCode($confirmationCode)->first();

        if(is_null($monitoredUser) || is_null($monitoredUser->email)){
            return redirect()->route('index')->withErrors('Invalid confirmation code');
        }

        $monitoredUser->confirmed = true;
        $monitoredUser->save();

        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerId($monitoredUser->region, $monitoredUser->summoner_id)->first();

        Session::flash('message', 'Your summoner is now being monitored and all games will be recorded!');
        Session::flash('message_color', 'green');

        return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_name]);
    }
}
