<?php

namespace App\Models;

/**
 * App\Models\Summoner
 *
 * @property integer $id
 * @property string $region
 * @property integer $summoner_id
 * @property string $internal_summoner_name
 * @property string $summoner_name
 * @property integer $profile_icon_id
 * @property integer $in_game_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SummonerGame[] $games
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereRegion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereSummonerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereInternalSummonerName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereSummonerName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereProfileIconId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereInGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner bySummonerId($region, $summonerID)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner bySummonerName($region, $summonerName)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Summoner byInternalName($region, $internalName)
 */
class Summoner extends \Eloquent
{
    /**
     * @var array
     */
    protected $fillable = array('region', 'summoner_id');

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $platformID
     * @param integer $summonerID
     */
    public function scopeBySummonerId($query, $region, $summonerID)
    {
        return $query->where('region', $region)->where('summoner_id', $summonerID);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $platformID
     * @param integer $summonerID
     */
    public function scopeBySummonerName($query, $region, $summonerName)
    {
        return $query->where('region', $region)->where('summoner_name', $summonerName);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $platformID
     * @param string $summonerID
     */
    public function scopeByInternalName($query, $region, $internalName)
    {
        return $query->where('region', $region)->where('internal_summoner_name', $internalName);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function games()
    {
        return $this->hasMany('App\Models\SummonerGame', 'db_summoner_id');
    }
}
