@extends('layout')

@section('title', 'Frequently Asked Questions')

@section('content')
    <ul class="collapsible" data-collapsible="accordion">
        <li>
            <div class="collapsible-header">What does SaveMyGame do?</div>
            <div class="collapsible-body"><p>SaveMyGame will automatically record all your games and their statistics for you to watch at any time.</p></div>
        </li>
        <li>
            <div class="collapsible-header">Do I need to install any software?</div>
            <div class="collapsible-body"><p>No we will record all your matches, and for you to watch them, you simply need to run a command or batch file.</p></div>
        </li>
        <li>
            <div class="collapsible-header">How does the process exactly work?</div>
            <div class="collapsible-body"><p>Simply search for your summoner name and press the video camera icon to add your summoner to be monitored by our system. All your games will be
                    automatically recorded without the need of your input at all. All matches will be recorded in spectator mode format and can be watched simply by choosing the game on your summoner
                    page and watching it by running a batch file or command which will make your LoL client connect to our replay servers.</p></div>
        </li>
        <li>
            <div class="collapsible-header">How long are the replays saved for?</div>
            <div class="collapsible-body"><p>Replays will be deleted only when a new patch comes out AND the replay is more than 7 days old. That means all replays of the current patch will be stored
                    regardless of how much time has passed. This is done because replays take up too much space on our servers, in addition to replays almost always not working on later patches of the game.
                    Game stats will be saved forever though, only the replay data will be deleted.</p></div>
        </li>
        <li>
            <div class="collapsible-header">Why does my LoL client go black after I watch a replay?</div>
            <div class="collapsible-body"><p>Unfortunately, this is something the client does when any replay is played and this is out of our control. However, this doesn't happen if you are on
                the post-game stats screen, so you may make want to watch your replays right after you finish your game.</p></div>
        </li>
        <li>
            <div class="collapsible-header">What are partial replays?</div>
            <div class="collapsible-body"><p>Partial replays allow you to watch just the moments you choose from a list of game events. This should save time from when you used search for that epic
                blind 360 no scope 420 blaze it baron steal in a 1-hour replay.</p></div>
        </li>
    </ul>
@endsection