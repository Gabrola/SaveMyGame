<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\KeyframeData
 *
 * @property integer $id
 * @property integer $keyframe_id
 * @property mixed $keyframe_data
 * @property-read \App\Models\Keyframe $keyframe
 * @method static \Illuminate\Database\Query\Builder|\App\Models\KeyframeData whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\KeyframeData whereKeyframeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\KeyframeData whereKeyframeData($value)
 */
class KeyframeData extends Eloquent
{
    protected $table = 'keyframe_data';
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function keyframe()
    {
        return $this->hasOne('App\Models\Keyframe');
    }
}
