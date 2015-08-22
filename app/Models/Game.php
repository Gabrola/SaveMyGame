<?php

namespace App\Models;

/**
 * App\Models\Game
 *
 * @property integer $id
 * @property string $platform_id
 * @property integer $game_id
 * @property string $encryption_key
 * @property integer $end_startup_chunk_id
 * @property integer $start_game_chunk_id
 * @property integer $end_game_chunk_id
 * @property integer $interest_score
 * @property string $start_stats
 * @property string $end_stats
 * @property string $events
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Chunk[] $chunks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Keyframe[] $keyframes
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereGameId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereEncryptionKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereEndStartupChunkId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereStartGameChunkId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereEndGameChunkId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereInterestScore($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereStartStats($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereEndStats($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereEvents($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Game byGame($platformID, $gameID)
 */
class Game extends \Eloquent
{
    /**
     * @var array
     */
    protected $fillable = array('platform_id', 'game_id', 'encryption_key', 'interest_score', 'end_stats', 'status');

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_stats' => 'array',
        'end_stats' => 'array',
        'events' => 'array',
        'game_id' => 'integer',
        'end_startup_chunk_id' => 'integer',
        'start_game_chunk_id' => 'integer',
        'end_game_chunk_id' => 'integer',
        'interest_score' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chunks()
    {
        return $this->hasMany('App\Models\Chunk', 'db_game_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keyframes()
    {
        return $this->hasMany('App\Models\Keyframe', 'db_game_id');
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

    public function deleteReplay()
    {
        $this->chunks()->getQuery()->delete();
        $this->keyframes()->getQuery()->delete();
        $this->status = 'deleted';
        $this->save();
    }
}