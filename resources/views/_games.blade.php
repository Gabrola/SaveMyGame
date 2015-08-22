@foreach($games as $game)
    {{--*/ $gameStats = $game->stats; /*--}}
    <a href="{{-- route('replay', [$summoner->region, $game->game_id]) --}}{{ action('SummonerController@getGame', [$game->region, $game->game_id]) }}" class="match_info {{ $game->win ? 'winning' : 'losing' }}">
        <div class="match_info_part">
            <span class="lol-champion-{{ $game->champion_id  }} img-roundshadow" title="{{ config('static.champions.' . $game->champion_id) }}"></span>
        </div>
        @if(!is_null($gameStats) && $gameStats)
            <div class="match_info_part">
                <div style="width: 200px;">{{ config('constants.queues.' . $game->queue_type, $game->queue_type) }}<br/><span title="{{ Carbon::createFromTimestamp($game->match_time)->toDayDateTimeString() }}">{{ Carbon::createFromTimestamp($game->match_time)->diffForHumans() }}</span></div>
            </div>
            <div class="match_info_part">
                <div style="width: 100px; font-size: 15px;">
                    <span title="Kills">{{ $gameStats['kills'] }}</span> /
                    <span title="Deaths">{{ $gameStats['deaths'] }}</span> /
                    <span title="Assists">{{ $gameStats['assists'] }}</span>
                </div>
            </div>
            <div class="match_info_part">
                <div style="width: 60px; font-size: 15px;">{{ \LeagueHelper::formatGold($gameStats['goldEarned']) }}</div>
                <div style="width: 60px; font-size: 12px;">Gold</div>
            </div>
            <div class="match_info_part">
                <div style="width: 60px; font-size: 15px;">{{ $gameStats['minionsKilled'] + $gameStats['neutralMinionsKilled'] }}</div>
                <div style="width: 60px; font-size: 12px;">CS</div>
            </div>
            <div class="match_info_part">
                <span class="lol-small-item-{{ $gameStats['item0'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item0']) }}"></span>
                <span class="lol-small-item-{{ $gameStats['item1'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item1']) }}"></span>
                <span class="lol-small-item-{{ $gameStats['item2'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item2']) }}"></span>
                <span class="lol-small-item-{{ $gameStats['item3'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item3']) }}"></span>
                <span class="lol-small-item-{{ $gameStats['item4'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item4']) }}"></span>
                <span class="lol-small-item-{{ $gameStats['item5'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item5']) }}"></span>
                <span class="lol-small-item-{{ $gameStats['item6'] }} img-roundshadow" title="{{ config('static.items.' . $gameStats['item6']) }}"></span>
            </div>
            <div class="match_info_part">
                <span class="lol-small-summoner-{{ $game->spell1 }} img-roundshadow" title="{{ config('static.spells.' . $game->spell1) }}"></span>
                <span class="lol-small-summoner-{{ $game->spell2 }} img-roundshadow" title="{{ config('static.spells.' . $game->spell2) }}"></span>
            </div>
        @else
            @if($game->queue_type != '')
                <div class="match_info_part">
                    <div style="width: 200px;">{{ config('constants.queues.' . $game->queue_type, $game->queue_type) }}<br/><span title="{{ Carbon::createFromTimestamp($game->match_time)->toDayDateTimeString() }}">{{ Carbon::createFromTimestamp($game->match_time)->diffForHumans() }}</span></div>
                </div>
            @endif
            <div class="match_info_part flex-fill">
                <div style="font-size: 20px;">End Game Stats Unavailable</div>
            </div>
        @endif
    </a>
@endforeach

@if($games->hasMorePages())
    <a class="btn waves-effect waves-light load-more-matches" href="{{ $games->nextPageUrl() }}"><i class="mdi mdi-hardware-keyboard-arrow-down right"></i>Load More</a>
@endif