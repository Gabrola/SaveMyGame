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

        return view('errors/generic')->withErrors('Summoner does not exist.');
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
                return view('errors/generic')->withErrors('Summoner does not exist.');
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
            return view('errors/generic')->withErrors('Summoner does not exist.');

        return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_name]);
    }

    public function getGame(Request $request, $region, $gameId)
    {
        if(!\LeagueHelper::regionExists($region))
            abort(404);

        /** @var \App\Models\Game $game */
        $game = Game::byGame(\LeagueHelper::getPlatformIdByRegion($region), $gameId)->first();

        if(is_null($game))
            return view('errors/generic')->withErrors('Game not found.');

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

        $command = sprintf('replay %s:80 %s %s %s', env('APP_DOMAIN', 'localhost'), $game->encryption_key, $game->game_id, $game->platform_id);
        $binaryData = pack('VVVVA*', 16, 1, 0, strlen($command), $command);
        $binaryArray = implode(',', unpack('C*', $binaryData));
        $cmdCommand = sprintf(config('constants.batfile2'), $binaryArray);

        return view('game', [
            'game' => $game,
            'command'   => $cmdCommand
        ]);
    }

    public function postRecord(Request $request)
    {
        $this->validate($request, [
            'region'                    => 'required|region',
            'summoner_id'               => 'required|integer',
            'email'                     => 'required|email|unique:monitored_users',
            'g-recaptcha-response'      => 'required|recaptcha'
        ]);

        $region = $request->input('region');
        $summonerId = $request->input('summoner_id');
        $email = $request->input('email');

        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerId($region, $summonerId)->first();

        if(is_null($summoner)) {
            if($request->ajax())
                abort(404);
            else
                return view('errors/generic')->withErrors('Summoner does not exist.');
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
        $monitoredUser->email = $email;
        $monitoredUser->confirmation_code = str_random(30);

        Mail::send('email.verify', ['confirmation_code' => $monitoredUser->confirmation_code], function($message) use ($monitoredUser, $summoner){
            $message->from('no-reply@savemyga.me', "SaveMyGa.me");
            $message->to($monitoredUser->email, $summoner->summoner_name);
            $message->subject('SaveMyGa.me Email Verification');
        });

        $monitoredUser->save();

        if($request->ajax()){
            return response()->json(true);
        } else {
            return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_id])
                ->with('message', 'The email has been sent.')
                ->with('message-color', 'green');
        }
    }

    public function verify($confirmationCode)
    {
        /** @var \App\Models\MonitoredUser $monitoredUser */
        $monitoredUser = MonitoredUser::whereConfirmationCode($confirmationCode)->first();

        if(is_null($monitoredUser)){
            return view('errors.generic')->withErrors('Invalid confirmation code');
        }

        $monitoredUser->confirmed = true;
        $monitoredUser->save();

        /** @var \App\Models\Summoner $summoner */
        $summoner = Summoner::bySummonerId($monitoredUser->region, $monitoredUser->summoner_id)->first();

        return response()->redirectToAction('SummonerController@getIndex', [$summoner->region, $summoner->summoner_name])
            ->with('message', 'Your summoner is now being monitored and all games will be recorded!')
            ->with('message-color', 'green');
    }
}
