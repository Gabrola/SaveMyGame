@extends('layout')

@section('title', $summoner->summoner_name)

@section('content')
    <div class="section">
        <h1 class="summoner-name valign-wrapper center-align">
            <img class="summoner-icon valign" src="//ddragon.leagueoflegends.com/cdn/{{ config('static.version') }}/img/profileicon/{{ $summoner->profile_icon_id }}.png">
            {{ $summoner->summoner_name }}
        </h1>
    </div>
    <div class="divider"></div>
    <div class="section center-align" id="games-container">
        <h3 class="center-align">Recent Matches</h3>

        @include('_games')
    </div>

    <div class="fixed-action-btn stick-bottom" style="bottom: 20px; right: 25px;">
        <a class="btn-floating btn-large red {{ $is_monitored ? 'disabled' : ''  }} tooltipped record-button" data-position="left" data-delay="10"
           data-tooltip="{{ $is_monitored ? 'All games are already recorded' : 'Start recording all matches'  }}"
                href="{{ action('SummonerController@getRecord', [$summoner->region, $summoner->summoner_id]) }}">
            <i class="mdi-av-videocam"></i>
        </a>
    </div>
@endsection