<?php

namespace App\Models;

/**
 * App\Models\MonitoredUser
 *
 * @property integer $id
 * @property string $region
 * @property integer $summoner_id
 * @property string $confirmation_code
 * @property boolean $confirmed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereRegion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereSummonerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereConfirmationCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereConfirmed($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MonitoredUser bySummonerId($region, $summonerID)
 */
class MonitoredUser extends \Eloquent
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'confirmed' => 'boolean',
    ];

    /**
     * @param string$value
     * @return string
     */
    public function getRegionAttribute($value)
    {
        return strtoupper($value);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $platformID
     * @param integer $summonerID
     */
    public function scopeBySummonerId($query, $region, $summonerID)
    {
        return $query->where('region', $region)->where('summoner_id', $summonerID);
    }
}