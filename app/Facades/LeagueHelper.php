<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class LeagueHelper extends Facade
{
    protected static function getFacadeAccessor() { return 'league'; }
}