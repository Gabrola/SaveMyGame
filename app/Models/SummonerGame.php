<?php

namespace App\Models;

/**
 * App\Models\SummonerGame
 *
 * @property integer $id
 * @property integer $db_summoner_id
 * @property string $region
 * @property integer $game_id
 * @property integer $summoner_id
 * @property integer $champion_id
 * @property boolean $win
 * @property boolean $map_id
 * @property string $queue_type
 * @property integer $match_time
 * @property integer $match_duration
 * @property array $stats
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereDbSummonerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereRegion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereSummonerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereChampionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereWin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereMapId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereQueueType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereMatchTime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereMatchDuration($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereStats($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereUpdatedAt($value)
 * @property-read \App\Models\Summoner $summoner
 * @property array $runes
 * @property array $masteries
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereRunes($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereMasteries($value)
 * @property integer $spell1
 * @property integer $spell2
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereSpell1($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SummonerGame whereSpell2($value)
 */
class SummonerGame extends \Eloquent
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'stats'     => 'array',
        'runes'     => 'array',
        'masteries' => 'array',
        'win'       => 'boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function summoner()
    {
        return $this->belongsTo('App\Models\Summoner', 'db_summoner_id');
    }
}
