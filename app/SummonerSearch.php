<?php

namespace App;

use App\Models\Summoner;
use GuzzleHttp\Client;

class SummonerSearch
{
    /**
     * @param string $region
     * @param string $summonerName
     * @return \App\Models\Summoner|boolean
     */
    public static function ByName($region, $summonerName)
    {
        if(!\LeagueHelper::regionExists($region))
            return false;

        config();

        $internalName = \LeagueHelper::getInternalName($summonerName);

        $summoner = Summoner::byInternalName($region, $internalName)->first();

        if(!is_null($summoner))
            return $summoner;

        $client = new Client();

        $requestUrl = 'https://' . \LeagueHelper::getApiByRegion($region) . '/api/lol/' . strtolower($region) . '/v1.4/summoner/by-name/' .
            $summonerName . '?api_key=' . env('RIOT_API_KEY');

        \Cache::add($requestUrl, true, 60);

        try {
            $res = $client->get($requestUrl);

            if($res->getStatusCode() != 200)
                return false;

            $json = json_decode($res->getBody(), true);

            if(!array_key_exists($internalName, $json))
                return false;

            $summonerData = $json[$internalName];

            $summoner = Summoner::bySummonerId($region, $summonerData['id'])->first();

            if(is_null($summoner)) {
                $summoner = new Summoner;
                $summoner->region = $region;
                $summoner->summoner_id = $summonerData['id'];
            }

            $summoner->internal_summoner_name = $internalName;
            $summoner->summoner_name = $summonerData['name'];
            $summoner->profile_icon_id = $summonerData['profileIconId'];
            $summoner->save();

            return $summoner;
        }
        catch(\Exception $e){
            return false;
        }
    }
}