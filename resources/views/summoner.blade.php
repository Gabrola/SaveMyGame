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
        <a class="btn-floating btn-large red {{ $isMonitored ? 'disabled' : ''  }} tooltipped modal-trigger" data-position="left" data-delay="10"
           data-tooltip="{{ $isMonitored ? 'All games are already recorded' : 'Start recording all matches'  }}"
                href="#confirm-modal">
            <i class="mdi-av-videocam"></i>
        </a>
    </div>

    @if(!$isMonitored)
        <div id="confirm-modal" class="modal">
            <div class="modal-content">
                <h4>Start Monitoring My Summoner</h4>
                <p>We will need you to rename one of your rune pages temporarily to confirm that you are the owner of the summoner. If you do not change your rune page name within 1 hour, your request will
                be deleted and you will have to request a new code.</p>
                <div class="row record-form-area">
                    @if($monitoredUser)
                        <div class="col s12"><p>Please rename one of your rune pages to <strong>{{ $monitoredUser->confirmation_code }}</strong> and it will be confirmed within one minute.
                                After it is confirmed you are free to rename your rune page.</p></div>
                    @else
                        <form id="record-form" action="{{ action('SummonerController@postRecord') }}" method="post" class="col s12 m6 offset-m3">
                            <input type="hidden" name="region" value="{{ $summoner->region }}">
                            <input type="hidden" name="summoner_id" value="{{ $summoner->summoner_id }}">
                            <div class="input-field record-recaptcha">
                                {!! Recaptcha::render() !!}
                            </div>
                            <div class="input-field center">
                                <button type="submit" class="btn waves-effect waves-light begin-record-submit">Begin</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="modal-close modal-action waves-effect waves-green btn-flat">Close</a>
            </div>
        </div>
    @endif
@endsection