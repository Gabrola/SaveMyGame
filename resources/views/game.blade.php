@extends('layout')

@section('title', 'Game Statistics')

@section('content')
    @if(!is_null($gameEndStats) && $gameEndStats)
        <div class="section">
            <h4 class="center">
                {{ config('constants.queues.' . $gameEndStats['queueType'], $gameEndStats['queueType']) }}
            </h4>
            <h5 class="center">Lasted {{ round($gameEndStats['matchDuration'] / 60) }} minutes on {{ Carbon::createFromTimestamp($gameEndStats['matchCreation'] / 1000)->toDayDateTimeString() }}</h5>
        </div>
    @else
        <div class="section">
            <h4 class="center">
                {{ config('constants.queueIds.' . (isset($gameStartStats['gameQueueConfigId']) ? $gameStartStats['gameQueueConfigId'] : 0)) }}
            </h4>
            <h5 class="center">On {{ Carbon::createFromTimestamp($gameStartStats['gameStartTime'] / 1000)->toDayDateTimeString() }}</h5>
        </div>
    @endif

    @if($gameEndStats && $gameStartStats)
        <div class="divider"></div>

        <div class="section">
            <div class="row game-row">
                <div class="col">
                    <div class="row">
                        <div class="col s12">
                            <h4>
                                @if(isset($gameEndStats['teams'][0]['winner']))
                                    {{ $gameEndStats['teams'][0]['winner'] ? 'Victory' : 'Defeat' }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    @foreach($gameEndStats['participants'] as $parIndex => $participant)
                        @if($participant['teamId'] == $gameEndStats['teams'][0]['teamId'])
                            {{--*/ $participantData = isset($gameStartStats['participants'][$parIndex]) ? $gameStartStats['participants'][$parIndex] : null; /*--}}
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
                        @if(!empty($gameStartStats['bannedChampions']))
                            <div class="col center">
                                <div>
                                    @foreach($gameStartStats['bannedChampions'] as $ban)
                                        @if($ban['teamId'] == $gameEndStats['teams'][0]['teamId'])
                                            <span class="lol-tiny-champion-{{ $ban['championId']  }} img-roundshadow left" title="{{ config('static.champions.' . $ban['championId']) }}"></span>
                                        @endif
                                    @endforeach
                                </div>
                                <div>Bans</div>
                            </div>
                        @endif

                        <div class="col center">
                            <div class="bans-value">{{ $gameEndStats['teams'][0]['towerKills'] }}</div>
                            <div>{{ Pluralizer::plural('Tower', $gameEndStats['teams'][0]['towerKills']) }}</div>
                        </div>

                        @if($gameEndStats['mapId'] == 11)
                            <div class="col center right">
                                <div class="bans-value">{{ $gameEndStats['teams'][0]['dragonKills'] }}</div>
                                <div>{{ Pluralizer::plural('Dragon', $gameEndStats['teams'][0]['dragonKills']) }}</div>
                            </div>

                            <div class="col center right">
                                <div class="bans-value">{{ $gameEndStats['teams'][0]['baronKills'] }}</div>
                                <div>{{ Pluralizer::plural('Baron', $gameEndStats['teams'][0]['baronKills']) }}</div>
                            </div>
                        @elseif($gameEndStats['mapId'] == 10)
                            <div class="col center right">
                                <div class="bans-value">{{ $gameEndStats['teams'][0]['vilemawKills'] }}</div>
                                <div>{{ Pluralizer::plural('Vilemaw', $gameEndStats['teams'][0]['vilemawKills']) }}</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col s12 right-align">
                            <h4>
                                @if(isset($gameEndStats['teams'][1]['winner']))
                                    {{ $gameEndStats['teams'][1]['winner'] ? 'Victory' : 'Defeat' }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    @foreach($gameEndStats['participants'] as $parIndex => $participant)
                        @if($participant['teamId'] == $gameEndStats['teams'][1]['teamId'])
                            {{--*/ $participantData = isset($gameStartStats['participants'][$parIndex]) ? $gameStartStats['participants'][$parIndex] : null; /*--}}
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
                            <div class="bans-value">{{ $gameEndStats['teams'][1]['towerKills'] }}</div>
                            <div>{{ Pluralizer::plural('Tower', $gameEndStats['teams'][1]['towerKills']) }}</div>
                        </div>

                        @if($gameEndStats['mapId'] == 11)
                            <div class="col center right">
                                <div class="bans-value">{{ $gameEndStats['teams'][1]['dragonKills'] }}</div>
                                <div>{{ Pluralizer::plural('Dragon', $gameEndStats['teams'][1]['dragonKills']) }}</div>
                            </div>

                            <div class="col center right">
                                <div class="bans-value">{{ $gameEndStats['teams'][1]['baronKills'] }}</div>
                                <div>{{ Pluralizer::plural('Baron', $gameEndStats['teams'][1]['baronKills']) }}</div>
                            </div>
                        @elseif($gameEndStats['mapId'] == 10)
                            <div class="col center right">
                                <div class="bans-value">{{ $gameEndStats['teams'][1]['vilemawKills'] }}</div>
                                <div>{{ Pluralizer::plural('Vilemaw', $gameEndStats['teams'][1]['vilemawKills']) }}</div>
                            </div>
                        @endif

                        @if(!empty($gameStartStats['bannedChampions']))
                            <div class="col center right">
                                <div>
                                    @foreach($gameStartStats['bannedChampions'] as $ban)
                                        @if($ban['teamId'] == $gameEndStats['teams'][1]['teamId'])
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

        @if($gameEndStats)
            @if(LeagueHelper::comparePatch(config('clientversion'), $gameEndStats['matchVersion']))
                <div class="card-panel red">
                    <span class="white-text">This match was recorded on an older patch (patch {{ LeagueHelper::getPatchFromVersion($gameEndStats['matchVersion']) }}). This replay will probably no longer work.</span>
                </div>
            @endif
        @endif

        <!-- Modal Structure -->
        <div id="alternative-modal" class="modal">
            @include('_replay_modal', ['windowsCommandId' => 'windowsCommand', 'macCommandId' => 'macCommand'])
        </div>

        @if(!empty($game->events))
            <div class="divider"></div>

            <div class="section events-section">
                <h4 class="center">Watch Partial Replay</h4>

                <h5 class="center">Filter By Killers</h5>
                <div class="champions-filter">
                    @foreach($gameEndStats['participants'] as $participant)
                        <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow filter-champion team-{{ $participant['teamId'] }} hide-shadow" data-player-id="{{ $participant['participantId'] }}" title="{{ config('static.champions.' . $participant['championId']) }}"></span>
                    @endforeach
                </div>

                <div class="center-align-children">
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
                            @foreach($game->events as $event)
                                @if($event['eventType'] == 'CHAMPION_KILL' && $event['killerId'] > 0)
                                    {{--*/ $killerParticipant = $gameEndStats['participants'][$event['killerId'] - 1]; /*--}}
                                    {{--*/ $killedParticipant = $gameEndStats['participants'][$event['victimId'] - 1]; /*--}}
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
                                        <td class="killer-td">
                                            <span class="lol-champion-{{ $killerParticipant['championId']  }} img-roundshadow team-{{ $killerParticipant['teamId'] }}" title="{{ config('static.champions.' . $killerParticipant['championId']) }}"></span>
                                            <span class="margin-20-sides">killed</span>
                                            <span class="lol-champion-{{ $killedParticipant['championId']  }} img-roundshadow team-{{ $killedParticipant['teamId'] }}" title="{{ config('static.champions.' . $killedParticipant['championId']) }}"></span>
                                        </td>
                                        <td class="check-td">
                                            <input type="checkbox" id="check-event-{{ $event['id'] }}" data-event-id="{{ $event['id'] }}" class="event-checkbox" />
                                            <label for="check-event-{{ $event['id'] }}"></label>
                                        </td>
                                    </tr>
                                @elseif($event['eventType'] == 'ELITE_MONSTER_KILL' && $event['killerId'] > 0)
                                    {{--*/ $killerParticipant = $gameEndStats['participants'][$event['killerId'] - 1]; /*--}}
                                    <tr id="row-event-{{ $event['id'] }}" data-type="monster" data-killer="{{ $event['killerId'] }}" data-event-id="{{ $event['id'] }}" data-timestamp="{{ $event['timestamp'] }}">
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
                                        <td class="center-align-children">
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
                    Time Leeway: <strong>15 seconds</strong> <i class="mdi-action-help" title="i.e. make sure that there are at least 15 seconds of time before and after the selected play"></i>
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
            <span class="white-text">This game's replay has been deleted because it has been recorded on an older patch and is more than 7 days old.</span>
        </div>
    @endif
@endsection