<?php

namespace App\Helpers;

class LeagueHelper
{
    /**
     * @var array
     */
    protected $regions = [
        'NA'    => [
            'domain'        => 'spectator.na.lol.riotgames.com:80',
            'platformId'    => 'NA1',
            'api'           => 'na.api.pvp.net',
            'name'          => 'North America',
            'releaseId'     => 'NA'
        ],

        'EUW'    => [
            'domain'        => 'spectator.euw1.lol.riotgames.com:80',
            'platformId'    => 'EUW1',
            'api'           => 'euw.api.pvp.net',
            'name'          => 'Europe West',
            'releaseId'     => 'EUW'
        ],

        'EUNE'    => [
            'domain'        => 'spectator.eu.lol.riotgames.com:8088',
            'platformId'    => 'EUN1',
            'api'           => 'eune.api.pvp.net',
            'name'          => 'Europe Nordic & East',
            'releaseId'     => 'EUNE'
        ],

        'KR'    => [
            'domain'        => 'spectator.kr.lol.riotgames.com:80',
            'platformId'    => 'KR',
            'api'           => 'kr.api.pvp.net',
            'name'          => 'Korea'
        ],

        'OCE'    => [
            'domain'        => 'spectator.oc1.lol.riotgames.com:80',
            'platformId'    => 'OC1',
            'api'           => 'oce.api.pvp.net',
            'name'          => 'Oceania',
            'releaseId'     => 'OC1'
        ],

        'BR'    => [
            'domain'        => 'spectator.br.lol.riotgames.com:80',
            'platformId'    => 'BR1',
            'api'           => 'br.api.pvp.net',
            'name'          => 'Brazil',
            'releaseId'     => 'BR'
        ],

        'LAN'    => [
            'domain'        => 'spectator.la1.lol.riotgames.com:80',
            'platformId'    => 'LA1',
            'api'           => 'lan.api.pvp.net',
            'name'          => 'Latin America North',
            'releaseId'     => 'LA1'
        ],

        'LAS'    => [
            'domain'        => 'spectator.la2.lol.riotgames.com:80',
            'platformId'    => 'LA2',
            'api'           => 'las.api.pvp.net',
            'name'          => 'Latin America South',
            'releaseId'     => 'LA2'
        ],

        'RU'    => [
            'domain'        => 'spectator.ru.lol.riotgames.com:80',
            'platformId'    => 'RU',
            'api'           => 'ru.api.pvp.net',
            'name'          => 'Russia',
            'releaseId'     => 'RU'
        ],

        'TR'    => [
            'domain'        => 'spectator.tr.lol.riotgames.com:80',
            'platformId'    => 'TR1',
            'api'           => 'tr.api.pvp.net',
            'name'          => 'Turkey',
            'releaseId'     => 'TR'
        ],

        /*'PBE'    => [
            'domain'        => 'spectator.pbe1.lol.riotgames.com:8080',
            'platformId'    => 'PBE1',
            'api'           => 'pbe.api.pvp.net'
        ]*/
    ];

    /**
     * @var array
     */
    protected $platformIds = [
        'NA1'   =>  'NA',
        'EUW1'  =>  'EUW',
        'EUN1'  =>  'EUNE',
        'KR'    =>  'KR',
        'OC1'   =>  'OCE',
        'BR1'   =>  'BR',
        'LA1'   =>  'LAN',
        'LA2'   =>  'LAS',
        'RU'    =>  'RU',
        'TR1'   =>  'TR',
        //'PBE1'  =>  'PBE'
    ];

    public function regionExists($region)
    {
        return array_key_exists(strtoupper($region), $this->regions);
    }

    public function getPatchFromVersion($version)
    {
        if(str_contains($version, '.'))
            return implode('.', array_slice(explode('.', $version), 0, 2));

        return $version;
    }

    public function comparePatch($version1, $version2, $operator = '>')
    {
        $version1Patch = $this->getPatchFromVersion($version1);
        $version2Patch = $this->getPatchFromVersion($version2);

        return version_compare($version1Patch, $version2Patch, $operator);
    }

    public function formatGold($gold)
    {
        if($gold < 1000)
            return $gold;
        else
            return number_format($gold / 1000, 1) . "k";
    }

    public function getAllServerNames()
    {
        $servers = [];

        foreach($this->regions as $region => $regionData)
            $servers[$region] = $regionData['name'];

        return $servers;
    }

    /**
     * @param string $region
     * @return string
     */
    public function getDomainByRegion($region)
    {
        $region = strtoupper($region);

        if(array_key_exists($region, $this->regions))
            return $this->regions[$region]['domain'];

        return false;
    }

    /**
     * @param string $region
     * @return string
     */
    public function getPlatformIdByRegion($region)
    {
        $region = strtoupper($region);

        if(array_key_exists($region, $this->regions))
            return $this->regions[$region]['platformId'];

        return false;
    }

    /**
     * @param string $region
     * @return string
     */
    public function getApiByRegion($region)
    {
        $region = strtoupper($region);

        if(array_key_exists($region, $this->regions))
            return $this->regions[$region]['api'];

        return false;
    }

    /**
     * @param string $region
     * @return string
     */
    public function getReleaseIdByRegion($region)
    {
        $region = strtoupper($region);

        if(array_key_exists($region, $this->regions))
            if(array_key_exists('releaseId', $this->regions[$region]))
                return $this->regions[$region]['releaseId'];

        return false;
    }

    /**
     * @param string $platformId
     * @return string
     */
    public function getDomainByPlatformId($platformId)
    {
        $platformId = strtoupper($platformId);

        if(array_key_exists($platformId, $this->platformIds))
            return $this->regions[$this->platformIds[$platformId]]['domain'];

        return false;
    }

    /**
     * @param string $platformId
     * @return string
     */
    public function getRegionByPlatformId($platformId)
    {
        $platformId = strtoupper($platformId);

        if(array_key_exists($platformId, $this->platformIds))
            return $this->platformIds[$platformId];

        return false;
    }

    /**
     * @param string $platformId
     * @return string
     */
    public function getApiByPlatformId($platformId)
    {
        $platformId = strtoupper($platformId);

        if(array_key_exists($platformId, $this->platformIds))
            return $this->regions[$this->platformIds[$platformId]]['api'];

        return false;
    }

    /**
     * @param string $summonerName
     * @return string
     */
    public function getInternalName($summonerName)
    {
        return str_replace(' ', '', mb_strtolower($summonerName));
    }
}