<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ChunkData
 *
 * @property integer $id
 * @property integer $chunk_id
 * @property mixed $chunk_data
 * @property-read \App\Models\Chunk $chunkInfo
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ChunkData whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ChunkData whereChunkId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ChunkData whereChunkData($value)
 */
class ChunkData extends Eloquent
{
    protected $table = 'chunk_data';
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function chunkInfo()
    {
        return $this->hasOne('App\Models\Chunk');
    }
}
