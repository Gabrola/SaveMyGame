<h4 class="center-align">{{ config('constants.queueIds.' . (isset($game->start_stats['gameQueueConfigId']) ? $game->start_stats['gameQueueConfigId'] : 0)) }}</h4>
<div class="row center-align-children">
    <div class="col">
        @foreach($game->start_stats['participants'] as $parIndex => $participant)
            @if($participant['teamId'] == 100)
                <div class="game-entry left-entry">
                    <div class="game-champion">
                        <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow" title="{{ config('static.champions.' . $participant['championId']) }}"></span>
                    </div>
                    <div class="game-spells valign-wrapper">
                        <div class="valign">
                            <span class="lol-tiny-summoner-{{ $participant['spell1Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell1Id']) }}"></span>
                            <span class="lol-tiny-summoner-{{ $participant['spell2Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell2Id']) }}"></span>
                        </div>
                    </div>
                    <div class="game-items valign-wrapper">
                        <a href="{{ action('SummonerController@getById', [LeagueHelper::getRegionByPlatformId($game->platform_id), $participant['summonerId']]) }}" class="valign">{{ $participant['summonerName'] }}</a><br>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="col">
        @foreach($game->start_stats['participants'] as $parIndex => $participant)
            @if($participant['teamId'] == 200)
                <div class="game-entry right-entry">
                    <div class="game-items valign-wrapper">
                        <a href="{{ action('SummonerController@getById', [LeagueHelper::getRegionByPlatformId($game->platform_id), $participant['summonerId']]) }}" class="valign purple-text">{{ $participant['summonerName'] }}</a><br>
                    </div>
                    <div class="game-spells valign-wrapper">
                        <div class="valign">
                            <span class="lol-tiny-summoner-{{ $participant['spell1Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell1Id']) }}"></span>
                            <span class="lol-tiny-summoner-{{ $participant['spell2Id'] }} img-roundshadow" title="{{ config('static.spells.' . $participant['spell2Id']) }}"></span>
                        </div>
                    </div>
                    <div class="game-champion">
                        <span class="lol-champion-{{ $participant['championId']  }} img-roundshadow" title="{{ config('static.champions.' . $participant['championId']) }}"></span>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>