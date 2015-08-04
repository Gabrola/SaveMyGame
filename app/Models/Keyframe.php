<?php

namespace App\Models;

/**
 * App\Models\Keyframe
 *
 * @property-read \App\Models\Game $game
 * @property integer $id
 * @property integer $db_game_id
 * @property string $platform_id
 * @property integer $game_id
 * @property integer $keyframe_id
 * @property mixed $keyframe_data
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereDbGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereKeyframeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereKeyframeData($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe byGame($platformID, $gameID)
 */
class Keyframe extends \Eloquent
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo('App\Models\Game', 'db_game_id');
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $platformID
     * @param integer $gameID
     */
    public function scopeByGame($query, $platformID, $gameID)
    {
        return $query->where('platform_id', $platformID)->where('game_id', $gameID);
    }
}