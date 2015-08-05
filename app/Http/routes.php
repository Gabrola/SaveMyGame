<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['as' => 'index', function(){

    $defaultRegion = key(\LeagueHelper::getAllServerNames());

    if(Request::hasCookie('search_region')){
        $regionCookie = Request::cookie('search_region');

        if(\LeagueHelper::regionExists($regionCookie))
            $defaultRegion = $regionCookie;
    }

    return View::make('index')->with([
        'defaultRegion'    => $defaultRegion
    ]);
}]);

Route::get('test', function(){
    config(['app.timezone' => 'Africa/Cairo']);

    return config('app.timezone');
});

Route::post('summoner/search', 'SummonerController@postSearch');
Route::get('summoner/{region}/{summonerName}', 'SummonerController@getIndex');
Route::get('summoner/id/{region}/{summonerId}', 'SummonerController@getById');
Route::get('summoner/record/{region}/{summonerId}', 'SummonerController@getRecord');
Route::get('game/{region}/{gameId}', 'SummonerController@getGame');
Route::get('faq', function(){
    return View::make('faq');
});

Route::get('replay/{region}-{matchId}.bat', ['as' => 'replay', function($region, $matchId){

    if(!$platformId = LeagueHelper::getPlatformIdByRegion($region))
        abort(404);

    /** @var \App\Models\Game $game */
    $game = \App\Models\Game::byGame($platformId, $matchId)->first();

    if(is_null($game))
        abort(404);

    $batFile = sprintf(config('constants.batfile'), env('APP_DOMAIN', 'localhost'), $game->encryption_key, $game->game_id, $game->platform_id);

    return Response::make($batFile, '200', array(
        'Content-Type' => 'application/x-bat',
        'Content-Disposition' => 'attachment; filename="REPLAY_' . $game->platform_id . $game->game_id . '.bat"'
    ));
}]);

Route::get('replay2/{region}-{matchId}.bat', ['as' => 'replay2', function($region, $matchId){

    if(!$platformId = LeagueHelper::getPlatformIdByRegion($region))
        abort(404);

    /** @var \App\Models\Game $game */
    $game = \App\Models\Game::byGame($platformId, $matchId)->first();

    if(is_null($game))
        abort(404);

    $command = sprintf('replay %s:80 %s %s %s', env('APP_DOMAIN', 'localhost'), $game->encryption_key, $game->game_id, $game->platform_id);
    $binaryData = pack('VVVVA*', 16, 1, 0, strlen($command), $command);
    $binaryArray = implode(',', unpack('C*', $binaryData));
    $batFile = sprintf(config('constants.batfile2'), $binaryArray);

    return Response::make($batFile, '200', array(
        'Content-Type' => 'application/x-bat',
        'Content-Disposition' => 'attachment; filename="REPLAY2_' . $game->platform_id . $game->game_id . '.bat"'
    ));
}]);

//Route::group(['domain' => '{randomID}.homestead.app'], function() {
    Route::group(['prefix' => 'observer-mode/rest/consumer'], function () {
        Route::get('version', 'SpectatorController@version');
        Route::get('getGameMetaData/{region}/{gameId}/{num}/token', 'SpectatorController@getGameMetaData');
        Route::get('getLastChunkInfo/{region}/{gameId}/{num}/token', 'SpectatorController@getLastChunkInfo');
        Route::get('getGameDataChunk/{region}/{gameId}/{num}/token', 'SpectatorController@getGameDataChunk');
        Route::get('getKeyFrame/{region}/{gameId}/{num}/token', 'SpectatorController@getKeyFrame');
        //Route::get('endOfGameStats/{region}/{gameId}/{ran}', 'SpectatorController@endOfGameStats');
    });
//});