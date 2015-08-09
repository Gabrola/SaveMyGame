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
Route::get('faq', 'PageController@faq');
Route::get('versions', 'PageController@versions');
Route::get('replay/{region}-{matchId}.bat', ['uses' => 'PageController@replay', 'as' => 'replay'] );

Route::group(['prefix' => 'observer-mode/rest/consumer'], function () {
    Route::get('version', 'SpectatorController@version');
    Route::get('getGameMetaData/{region}/{gameId}/{num}/token', 'SpectatorController@getGameMetaData');
    Route::get('getLastChunkInfo/{region}/{gameId}/{num}/token', 'SpectatorController@getLastChunkInfo');
    Route::get('getGameDataChunk/{region}/{gameId}/{num}/token', 'SpectatorController@getGameDataChunk');
    Route::get('getKeyFrame/{region}/{gameId}/{num}/token', 'SpectatorController@getKeyFrame');
});