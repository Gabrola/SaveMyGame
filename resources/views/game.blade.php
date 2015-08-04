@extends('layout')

@section('title', 'Game Statistics')

@section('content')
    @if(!is_null($game->end_stats) && $game->end_stats)
        <div class="section">
            <h4 class="center">
                {{ config('constants.queues.' . $game->end_stats['queueType'], $game->end_stats['queueType']) }}
            </h4>
            <h5 class="center">Lasted {{ round($game->end_stats['matchDuration'] / 60) }} minutes on {{ date('d F, Y H:i:s', $game->end_stats['matchCreation'] / 1000) }}</h5>
        </div>
    @else
        <div class="section">
            <h4 class="center">
                {{ config('constants.queueIds.' . (isset($game->start_stats['gameQueueConfigId']) ? $game->start_stats['gameQueueConfigId'] : 0)) }}
            </h4>
            <h5 class="center">On {{ date('d F, Y H:i:s', $game->start_stats['gameStartTime'] / 1000) }}</h5>
        </div>
    @endif

    @if(!is_null($game->end_stats) && $game->end_stats && $game->start_stats)
        <div class="divider"></div>

        <div class="section">
            <div class="row">
                <div class="col l6">
                    <div class="row">
                        <div class="col s12"><h4>{{ $game->end_stats['teams'][0]['winner'] ? 'Victory' : 'Defeat' }}</h4></div>
                    </div>
                    @foreach($game->end_stats['participants'] as $parIndex => $participant)
                        @if($participant['teamId'] == $game->end_stats['teams'][0]['teamId'])
                            {{--*/ $participantData = $game->start_stats['participants'][$parIndex] /*--}}
                            <div class="game-entry left-entry">
                                <div class="game-champion">
                                    <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow" title="{{ config('static.champions.' . $participant['championId']) }}"></span>
                                    <span class="game-champion-level">{{ $participant['stats']['champLevel'] }}</span>
                                </div>
                                <div class="game-spells valign-wrapper">
                                    <div class="valign">
                                        <span class="lol-tiny-summoner-{{ $participant['spell1Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell1Id']) }}"></span>
                                        <span class="lol-tiny-summoner-{{ $participant['spell2Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell2Id']) }}"></span>
                                    </div>
                                </div>
                                <div class="game-items">
                                    <a href="{{ action('SummonerController@getById', [LeagueHelper::getRegionByPlatformId($game->platform_id), $participantData['summonerId']]) }}">{{ $participantData['summonerName'] }}</a><br>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item0'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item0']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item1'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item1']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item2'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item2']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item3'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item3']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item4'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item4']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item5'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item5']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item6'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item6']) }}"></span>
                                </div>
                                <div class="game-kda">
                                    <span>
                                        {{ $participant['stats']['kills'] }} / {{ $participant['stats']['deaths'] }} / {{ $participant['stats']['assists'] }}<br>
                                        <span>KDA</span>
                                    </span>
                                </div>
                                <div class="game-cs">
                                    <span>
                                        {{ $participant['stats']['minionsKilled'] + $participant['stats']['neutralMinionsKilled'] }}<br>
                                        <span>CS</span>
                                    </span>
                                </div>
                                <div class="game-gold">
                                    <span>
                                        {{ \LeagueHelper::formatGold($participant['stats']['goldEarned']) }}<br>
                                        <span>Gold</span>
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    <div class="row bans-left">
                        <div class="col s12">
                            @if(!empty($game->start_stats['bannedChampions']))
                                <div class="col center">
                                    <div>
                                        @foreach($game->start_stats['bannedChampions'] as $ban)
                                            @if($ban['teamId'] == $game->end_stats['teams'][0]['teamId'])
                                                <span class="lol-tiny-champion-{{ $ban['championId']  }} img-roundshadow left" title="{{ config('static.champions.' . $ban['championId']) }}"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div>Bans</div>
                                </div>
                            @endif

                            <div class="col center">
                                <div class="bans-value">{{ $game->end_stats['teams'][0]['towerKills'] }}</div>
                                <div>Towers</div>
                            </div>

                            <div class="col center">
                                <div class="bans-value">{{ $game->end_stats['teams'][0]['dragonKills'] }}</div>
                                <div>Dragons</div>
                            </div>

                            <div class="col center">
                                <div class="bans-value">{{ $game->end_stats['teams'][0]['baronKills'] }}</div>
                                <div>Barons</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col l6">
                    <div class="row">
                        <div class="col s12 right-align"><h4>{{ $game->end_stats['teams'][1]['winner'] ? 'Victory' : 'Defeat' }}</h4></div>
                    </div>
                    @foreach($game->end_stats['participants'] as $parIndex => $participant)
                        @if($participant['teamId'] == $game->end_stats['teams'][1]['teamId'])
                            {{--*/ $participantData = $game->start_stats['participants'][$parIndex] /*--}}
                            <div class="game-entry right-entry">
                                <div class="game-gold">
                                    <span>
                                        {{ \LeagueHelper::formatGold($participant['stats']['goldEarned']) }}<br>
                                        <span>Gold</span>
                                    </span>
                                </div>
                                <div class="game-cs">
                                    <span>
                                        {{ $participant['stats']['minionsKilled'] + $participant['stats']['neutralMinionsKilled'] }}<br>
                                        <span>CS</span>
                                    </span>
                                </div>
                                <div class="game-kda">
                                    <span>
                                        {{ $participant['stats']['kills'] }} / {{ $participant['stats']['deaths'] }} / {{ $participant['stats']['assists'] }}<br>
                                        <span>KDA</span>
                                    </span>
                                </div>
                                <div class="game-items">
                                    <a href="{{ action('SummonerController@getById', [LeagueHelper::getRegionByPlatformId($game->platform_id), $participantData['summonerId']]) }}">{{ $participantData['summonerName'] }}</a><br>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item0'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item0']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item1'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item1']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item2'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item2']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item3'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item3']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item4'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item4']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item5'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item5']) }}"></span>
                                    <span class="lol-tiny-item-{{ $participant['stats']['item6'] }} img-roundshadow left" title="{{ config('static.items.' . $participant['stats']['item6']) }}"></span>
                                </div>
                                <div class="game-spells valign-wrapper">
                                    <div class="valign">
                                        <span class="lol-tiny-summoner-{{ $participant['spell1Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell1Id']) }}"></span>
                                        <span class="lol-tiny-summoner-{{ $participant['spell2Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell2Id']) }}"></span>
                                    </div>
                                </div>
                                <div class="game-champion">
                                    <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow" title="{{ config('static.champions.' . $participant['championId']) }}"></span>
                                    <span class="game-champion-level">{{ $participant['stats']['champLevel'] }}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    <div class="row bans-right">
                        <div class="col s12">
                            @if(!empty($game->start_stats['bannedChampions']))
                                <div class="col center right">
                                    <div>
                                        @foreach($game->start_stats['bannedChampions'] as $ban)
                                            @if($ban['teamId'] == $game->end_stats['teams'][1]['teamId'])
                                                <span class="lol-tiny-champion-{{ $ban['championId']  }} img-roundshadow left" title="{{ config('static.champions.' . $ban['championId']) }}"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div>Bans</div>
                                </div>
                            @endif

                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][1]['towerKills'] }}</div>
                                <div>Towers</div>
                            </div>

                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][1]['dragonKills'] }}</div>
                                <div>Dragons</div>
                            </div>

                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][1]['baronKills'] }}</div>
                                <div>Barons</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card-panel red">
            <span class="white-text">Match details not available.</span>
        </div>
    @endif

    <div class="divider"></div>

    <div class="section center">
        <a class="btn waves-effect waves-light" href="http://matchhistory.na.leagueoflegends.com/en/#match-details/{{ $game->platform_id }}/{{ $game->game_id }}" target="_blank"><i class="mdi-action-assessment left"></i> Full Match Details</a>
        <a class="btn waves-effect waves-light red" href="#"><i class="mdi-av-videocam left"></i> Watch Replay</a>
    </div>
@endsection