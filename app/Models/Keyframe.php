<?php

namespace App\Models;

/**
 * App\Models\Keyframe
 *
 * @property integer $id
 * @property integer $db_game_id
 * @property string $platform_id
 * @property integer $game_id
 * @property integer $keyframe_id
 * @property-read \App\Models\Game $game
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereDbGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Keyframe whereKeyframeId($value)
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
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'game_id' => 'integer',
        'keyframe_id' => 'integer',
    ];

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