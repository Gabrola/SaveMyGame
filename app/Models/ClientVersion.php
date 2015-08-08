<?php

namespace App\Models;

use Eloquent;

/**
 * App\Models\ClientVersion
 *
 * @property integer $id
 * @property string $client_version
 * @property string $release_version
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ClientVersion whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ClientVersion whereClientVersion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ClientVersion whereReleaseVersion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ClientVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\ClientVersion whereUpdatedAt($value)
 */
class ClientVersion extends Eloquent
{

}
