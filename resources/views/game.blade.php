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

    @if($game->end_stats && $game->start_stats)
        <div class="divider"></div>

        <div class="section">
            <div class="row game-row">
                <div class="col">
                    <div class="row">
                        <div class="col s12">
                            <h4>
                                @if(isset($game->end_stats['teams'][0]['winner']))
                                    {{ $game->end_stats['teams'][0]['winner'] ? 'Victory' : 'Defeat' }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    @foreach($game->end_stats['participants'] as $parIndex => $participant)
                        @if($participant['teamId'] == $game->end_stats['teams'][0]['teamId'])
                            {{--*/ $participantData = isset($game->start_stats['participants'][$parIndex]) ? $game->start_stats['participants'][$parIndex] : null; /*--}}
                            <div class="game-entry left-entry">
                                <div class="game-champion">
                                    <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow" title="{{ config('static.champions.' . $participant['championId']) }}"><span class="game-champion-level">{{ $participant['stats']['champLevel'] }}</span></span>
                                </div>
                                <div class="game-spells valign-wrapper">
                                    <div class="valign">
                                        <span class="lol-tiny-summoner-{{ $participant['spell1Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell1Id']) }}"></span>
                                        <span class="lol-tiny-summoner-{{ $participant['spell2Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell2Id']) }}"></span>
                                    </div>
                                </div>
                                <div class="game-items">
                                    @if($participantData)
                                        <a href="{{ action('SummonerController@getById', [LeagueHelper::getRegionByPlatformId($game->platform_id), $participantData['summonerId']]) }}">{{ $participantData['summonerName'] }}</a><br>
                                    @endif
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
                    <div class="row bans-left center-align-children">
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
                            <div>{{ Pluralizer::plural('Tower', $game->end_stats['teams'][0]['towerKills']) }}</div>
                        </div>

                        @if($game->end_stats['mapId'] == 11)
                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][0]['dragonKills'] }}</div>
                                <div>{{ Pluralizer::plural('Dragon', $game->end_stats['teams'][0]['dragonKills']) }}</div>
                            </div>

                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][0]['baronKills'] }}</div>
                                <div>{{ Pluralizer::plural('Baron', $game->end_stats['teams'][0]['baronKills']) }}</div>
                            </div>
                        @elseif($game->end_stats['mapId'] == 10)
                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][0]['vilemawKills'] }}</div>
                                <div>{{ Pluralizer::plural('Vilemaw', $game->end_stats['teams'][0]['vilemawKills']) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col s12 right-align">
                            <h4>
                                @if(isset($game->end_stats['teams'][1]['winner']))
                                    {{ $game->end_stats['teams'][1]['winner'] ? 'Victory' : 'Defeat' }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    @foreach($game->end_stats['participants'] as $parIndex => $participant)
                        @if($participant['teamId'] == $game->end_stats['teams'][1]['teamId'])
                            {{--*/ $participantData = isset($game->start_stats['participants'][$parIndex]) ? $game->start_stats['participants'][$parIndex] : null; /*--}}
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
                                    @if($participantData)
                                        <a href="{{ action('SummonerController@getById', [LeagueHelper::getRegionByPlatformId($game->platform_id), $participantData['summonerId']]) }}" class="purple-text">{{ $participantData['summonerName'] }}</a><br>
                                    @endif
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
                                    <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow" title="{{ config('static.champions.' . $participant['championId']) }}"><span class="game-champion-level">{{ $participant['stats']['champLevel'] }}</span></span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    <div class="row bans-right center-align-children">
                        <div class="col center right">
                            <div class="bans-value">{{ $game->end_stats['teams'][1]['towerKills'] }}</div>
                            <div>{{ Pluralizer::plural('Tower', $game->end_stats['teams'][1]['towerKills']) }}</div>
                        </div>

                        @if($game->end_stats['mapId'] == 11)
                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][1]['dragonKills'] }}</div>
                                <div>{{ Pluralizer::plural('Dragon', $game->end_stats['teams'][1]['dragonKills']) }}</div>
                            </div>

                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][1]['baronKills'] }}</div>
                                <div>{{ Pluralizer::plural('Baron', $game->end_stats['teams'][1]['baronKills']) }}</div>
                            </div>
                        @elseif($game->end_stats['mapId'] == 10)
                            <div class="col center right">
                                <div class="bans-value">{{ $game->end_stats['teams'][1]['vilemawKills'] }}</div>
                                <div>{{ Pluralizer::plural('Vilemaw', $game->end_stats['teams'][1]['vilemawKills']) }}</div>
                            </div>
                        @endif

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
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card-panel red">
            <span class="white-text">Match details not available.</span>
        </div>
    @endif

    @if($game->status == 'downloaded')
        <div class="divider"></div>

        <div class="section center">
            <a class="btn waves-effect waves-light" href="http://matchhistory.na.leagueoflegends.com/en/#match-details/{{ $game->platform_id }}/{{ $game->game_id }}" target="_blank"><i class="mdi-action-assessment left"></i> Full Match Details</a>
            <a class="btn waves-effect waves-light red modal-trigger" href="#alternative-modal"><i class="mdi-av-videocam left"></i> Watch Replay</a>
        </div>

        @if($game->end_stats)
            @if(LeagueHelper::comparePatch(config('clientversion'), $game->end_stats['matchVersion']))
                <div class="card-panel red">
                    <span class="white-text">This match was recorded on an older patch. This replay will probably no longer work.</span>
                </div>
            @endif
        @endif

        <!-- Modal Structure -->
        <div id="alternative-modal" class="modal">
            @include('_replay_modal', ['windowsCommandId' => 'windowsCommand', 'macCommandId' => 'macCommand'])
        </div>

        @if(!empty($events))
            <div class="divider"></div>

            <div class="section">
                <h4 class="center">Partial Replay</h4>

                <style scoped="scoped">
                    .event-table {
                        width: 500px;
                    }

                    .slimScrollDiv, .jqstb-scroll {
                        display: inline-block;
                    }

                    table.event-table td:not(:last-child) {
                        padding-right: 20px;
                    }

                    .check-td {
                        padding-top: 22px;
                    }

                    .champions-filter {
                        display: flex; justify-content: center;
                        flex-wrap: wrap;
                    }

                    .champions-filter > *{ margin: 2px 10px; }
                </style>


                <h5 class="center">Filter By Kills</h5>
                <div class="champions-filter">
                    @foreach($game->end_stats['participants'] as $participant)
                        <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow filter-champion team-{{ $participant['teamId'] }} hide-shadow" data-player-id="{{ $participant['participantId'] }}" title="{{ config('static.champions.' . $participant['championId']) }}"></span>
                    @endforeach
                </div>

                <div style="display: flex; justify-content: center;">
                    <div>
                        <table class="event-table">
                            <thead>
                            <tr>
                                <th>Time</th>
                                <th>Event Type</th>
                                <th>Event Information</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody class="event-tbody">
                            @foreach($events as $event)
                                @if($event['eventType'] == 'CHAMPION_KILL' && $event['killerId'] > 0)
                                    {{--*/ $killerParticipant = $game->end_stats['participants'][$event['killerId'] - 1]; /*--}}
                                    {{--*/ $killedParticipant = $game->end_stats['participants'][$event['victimId'] - 1]; /*--}}
                                    <tr id="row-event-{{ $event['id'] }}" data-type="kill" data-killer="{{ $event['killerId'] }}" data-event-id="{{ $event['id'] }}" data-timestamp="{{ $event['timestamp'] }}">
                                        <td>{{ gmdate("i:s", floor($event['timestamp'] / 1000)) }}</td>
                                        <td>
                                            @if($event['multiKill'] == 1)
                                                <span>Champion Kill</span>
                                            @elseif($event['multiKill'] == 2)
                                                <span class="bold blue-text">Double Kill</span>
                                            @elseif($event['multiKill'] == 3)
                                                <span class="bold blue-text">Triple Kill</span>
                                            @elseif($event['multiKill'] == 4)
                                                <span class="extra-bold red-text">Quadra Kill</span>
                                            @elseif($event['multiKill'] == 5)
                                                <span class="extra-bold red-text">Penta Kill</span>
                                            @elseif($event['multiKill'] > 5)
                                                <span class="extra-bold red-text">Legendary Kill</span>
                                            @endif
                                        </td>
                                        <td style="display: flex; align-items: center">
                                            <span class="lol-champion-{{ $killerParticipant['championId']  }} img-roundshadow team-{{ $killerParticipant['teamId'] }}" title="{{ config('static.champions.' . $killerParticipant['championId']) }}"></span>
                                            <span style="margin: 0 20px">killed</span>
                                            <span class="lol-champion-{{ $killedParticipant['championId']  }} img-roundshadow team-{{ $killedParticipant['teamId'] }}" title="{{ config('static.champions.' . $killedParticipant['championId']) }}"></span>
                                        </td>
                                        <td class="check-td">
                                            <input type="checkbox" id="check-event-{{ $event['id'] }}" data-event-id="{{ $event['id'] }}" class="event-checkbox" />
                                            <label for="check-event-{{ $event['id'] }}"></label>
                                        </td>
                                    </tr>
                                @elseif($event['eventType'] == 'ELITE_MONSTER_KILL')
                                    {{--*/ $killerParticipant = $game->end_stats['participants'][$event['killerId'] - 1]; /*--}}
                                    <tr id="row-event-{{ $event['id'] }}" data-type="monster" data-killer="{{ $event['killerId'] }}" data-event-id="{{ $event['id'] }}">
                                        <td>{{ gmdate("i:s", floor($event['timestamp'] / 1000)) }}</td>
                                        <td>
                                            @if($event['monsterType'] == 'BARON_NASHOR')
                                                <span class="bold purple-text">Baron Kill</span>
                                            @elseif($event['monsterType'] == 'DRAGON')
                                                <span class="bold brown-text">Dragon Kill</span>
                                            @elseif($event['monsterType'] == 'VILEMAW')
                                                <span class="bold brown-text">Vilemaw Kill</span>
                                            @endif
                                        </td>
                                        <td style="display: flex; align-items: center">
                                            <span class="lol-champion-{{ $killerParticipant['championId']  }} img-roundshadow team-{{ $killerParticipant['teamId']  }}" title="{{ config('static.champions.' . $killerParticipant['championId']) }}"></span>
                                        </td>
                                        <td class="check-td">
                                            <input type="checkbox" id="check-event-{{ $event['id'] }}" data-event-id="{{ $event['id'] }}" class="event-checkbox" />
                                            <label for="check-event-{{ $event['id'] }}"></label>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>


                <p class="center">
                    Time Leeway: <strong>15 seconds</strong> <i class="mdi-action-help" title="i.e. make sure that there is at least 10 seconds of time before and after the selected play"></i>
                    <br>Replay Interval: <span class="replay-begin-time blue-text">00:00</span> (-15 seconds) to <span class="replay-end-time blue-text">00:00</span> (+15 seconds)
                </p>
                <p class="center">
                    <a class="btn waves-effect waves-light red" style="display: none" href="#" id="watch-interval-btn"><i class="mdi-av-videocam left"></i> Watch Partial Replay</a>
                </p>
            </div>

            <div id="partial-modal" class="modal"></div>
        @endif
    @else
        <div class="card-panel red">
            <span class="white-text">This game's replay is corrupted or has been deleted because it has been recorded on an older patch and is more than 7 days old.</span>
        </div>
    @endif
@endsection