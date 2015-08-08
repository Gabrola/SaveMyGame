@extends('layout')

@section('title', 'Index')

@section('content')
    <form action="{{ action('SummonerController@postSearch')  }}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="region" id="search_region" value="{{ $defaultRegion  }}">

        <div class="row">
            <div class="col l6 offset-l3 m8 offset-m2 s12">
                <div class="row">
                    <div class="input-field col s12">
                        <div class="search-area">
                            <div class="search-text">
                                <a class="waves-effect waves-light btn dropdown-button server-button" href="#" data-activates="server-dropdown">
                                    <span class="current-server">{{ $defaultRegion  }}</span>
                                </a>
                                <ul id="server-dropdown" class="dropdown-content">
                                    @foreach(\LeagueHelper::getAllServerNames() as $region => $name)
                                        <li><a href="#" class="server-choice" data-region="{{ $region }}">{{ $name  }}</a></li>
                                    @endforeach
                                </ul>
                                <input id="summoner_name" name="summoner_name" type="text" >
                                <label for="summoner_name">Summoner Name</label>
                            </div>
                            <button class="btn-floating waves-effect waves-light" type="submit" name="action">
                                <i class="mdi-action-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col offset-m1 m10"><p class="flow-text center">SaveMyGa.me is a service that will automatically record all of your League of Legends game replays without needing you to download or install any 3rd party applications!</p></div>
    </div>
@endsection