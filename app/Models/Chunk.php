<?php

namespace App\Models;

/**
 * App\Models\Chunk
 *
 * @property integer $id 
 * @property integer $db_game_id 
 * @property string $platform_id 
 * @property integer $game_id 
 * @property integer $chunk_id 
 * @property integer $keyframe_id 
 * @property integer $next_chunk_id 
 * @property integer $duration 
 * @property-read \App\Models\Game $game 
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereDbGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereChunkId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereKeyframeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereNextChunkId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk whereDuration($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk startGame($startGameId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk lastChunk()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Chunk byGame($platformID, $gameID)
 */
class Chunk extends \Eloquent
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
        'chunk_id' => 'integer',
        'keyframe_id' => 'integer',
        'next_chunk_id' => 'integer',
        'duration' => 'integer',
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
     * @param integer $startGameId
     */
    public function scopeStartGame($query, $startGameId)
    {
        return $query->where('chunk_id', '>=', $startGameId)->orderBy('chunk_id', 'asc');
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeLastChunk($query)
    {
        return $query->orderBy('chunk_id', 'desc');
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