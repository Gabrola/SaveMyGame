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

Route::get('/', ['uses' => 'PageController@index', 'as' => 'index']);
Route::post('summoner/search', 'SummonerController@postSearch');
Route::get('summoner/{region}/{summonerName}', 'SummonerController@getIndex');
Route::get('summoner/id/{region}/{summonerId}', 'SummonerController@getById');
Route::post('summoner/record', 'SummonerController@postRecord');
Route::get('game/{region}/{gameId}', 'SummonerController@getGame');
Route::get('game/{region}/{gameId}/events/{timestamp1}/{timestamp2}', 'SummonerController@getGameEvents')
    ->where(['timestamp1' => '[0-9]+', 'timestamp2' => '[0-9]+']);
Route::get('faq', 'PageController@faq');
Route::get('donate', 'PageController@donate');
Route::get('versions', 'PageController@versions');
Route::get('replay/{region}-{matchId}.bat', ['uses' => 'PageController@replay', 'as' => 'replay'] );
Route::get('replayAlt/{region}-{matchId}.bat', ['uses' => 'PageController@replayAlt', 'as' => 'replayAlt'] );
Route::get('replayPartial/{region}-{matchId}-{partial}.bat', ['uses' => 'PageController@replayPartial', 'as' => 'replayPartial'] )
    ->where(['partial' => '[0-9]+']);
Route::get('gabrolatest', 'PageController@test');

Route::group(['prefix' => 'observer-mode/rest/consumer'], function () {
    Route::get('version', 'SpectatorController@version');
    Route::get('getGameMetaData/{region}/{gameId}/{num}/token', 'SpectatorController@getGameMetaData');
    Route::get('getLastChunkInfo/{region}/{gameId}/{num}/token', 'SpectatorController@getLastChunkInfo');
    Route::get('getGameDataChunk/{region}/{gameId}/{num}/token', 'SpectatorController@getGameDataChunk');
    Route::get('getKeyFrame/{region}/{gameId}/{num}/token', 'SpectatorController@getKeyFrame');
});