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
                <div class="col l6">
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
                <div class="col l6">
                    <div class="row">
                        <div class="col s12">
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

    @if($game->status != 'deleted')
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
            <div class="modal-content">
                <h4>Watch Replay</h4>
                <ul class="collapsible" data-collapsible="accordion">
                    <li>
                        <div class="collapsible-header">Windows Command</div>
                        <div class="collapsible-body modal-collapse-body">
                            <p>Open a command prompt, paste this into it and press enter. Make sure your League of Legends client is running.</p>
                            <div style="display: flex">
                                <textarea id="windowsCommand" rows="1" readonly class="command-area" onclick="this.focus();this.select()">{{ $windowsCommand }}</textarea>
                                <i class="mdi-content-content-copy copy-button" data-copy-element="windowsCommand" data-zclip-path="{{ asset('build/js/ZeroClipboard.swf') }}"></i>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="collapsible-header">Windows Batch File</div>
                        <div class="collapsible-body modal-collapse-body">
                            <a class="btn waves-effect waves-light red" href="{{ route('replay' . ($useAlt ? 'Alt' : ''), [ LeagueHelper::getRegionByPlatformId($game->platform_id), $game->game_id ]) }}"><i class="mdi-file-file-download left"></i> Download</a>
                        </div>
                    </li>
                    <li>
                        <div class="collapsible-header">Mac Command</div>
                        <div class="collapsible-body modal-collapse-body">
                            <p>Open a terminal window, paste this into it and press enter. Make sure your League of Legends client is running.</p>
                            <div style="display: flex">
                                <textarea id="macCommand" rows="1" readonly class="command-area" onclick="this.focus();this.select()">{{ $macCommand }}</textarea>
                                <i class="mdi-content-content-copy copy-button" data-copy-element="macCommand" data-zclip-path="{{ asset('build/js/ZeroClipboard.swf') }}"></i>
                            </div>
                        </div>
                    </li>
                </ul>

            </div>
            <div class="modal-footer">
                <a href="#" class="modal-action modal-close waves-effect waves-green btn-flat ">Close</a>
            </div>
        </div>
    @else
        <div class="card-panel red">
            <span class="white-text">This game's replay has been deleted because it has been recorded on an older patch and is more than 7 days old.</span>
        </div>
    @endif
@endsection