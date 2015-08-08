@extends('layout')

@section('title', $summoner->summoner_name)

@section('content')
    <div class="section">
        <h1 class="summoner-name valign-wrapper center-align">
            <img class="summoner-icon valign" src="//ddragon.leagueoflegends.com/cdn/{{ config('static.version') }}/img/profileicon/{{ $summoner->profile_icon_id }}.png">
            {{ $summoner->summoner_name }}
        </h1>
    </div>

    @if($inGame)
        <div class="divider"></div>
        <div class="section center-align" id="games-container">
            <h3 class="center-align">Current Match <span class="red-text"><i class="mdi-image-lens rec-dot"></i> REC</span></h3>
            @include('_in_game', ['game' => $inGameData])
        </div>
    @endif

    <div class="divider"></div>
    <div class="section center-align" id="games-container">
        <h3 class="center-align">Recent Matches</h3>

        @include('_games')
    </div>

    <div class="fixed-action-btn stick-bottom" style="bottom: 20px; right: 25px;">
        <a class="btn-floating btn-large red {{ $is_monitored ? 'disabled' : ''  }} tooltipped modal-trigger" data-position="left" data-delay="10"
           data-tooltip="{{ $is_monitored ? 'All games are already recorded' : 'Start recording all matches'  }}"
                href="#email-modal">
            <i class="mdi-av-videocam"></i>
        </a>
    </div>

    @if(!$is_monitored)
        <div id="email-modal" class="modal">
            <div class="modal-content">
                <h4>Add Summoner to Monitored Summoners</h4>
                <p>We only need your email to verify to prevent anyone from abusing our service. We will not send you any spam, we promise. We will send you a confirmation link to verify if the entered email is valid and that's it.</p>
                <div class="row">
                    <form id="record-form" action="{{ action('SummonerController@postRecord') }}" method="post" class="col s12 m6 offset-m3">
                        <input type="hidden" name="region" value="{{ $summoner->region }}">
                        <input type="hidden" name="summoner_id" value="{{ $summoner->summoner_id }}">
                        <div class="input-field">
                            <input name="email" id="record-email" type="email" class="validate">
                            <label for="record-email">Email</label>
                        </div>
                        <div class="input-field record-recaptcha">
                            {!! Recaptcha::render() !!}
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="modal-close modal-action waves-effect waves-green btn-flat">Close</a>
                <a href="#" class="btn-flat waves-effect waves-green record-form-submit">Submit</a>
            </div>
        </div>
    @endif
@endsection