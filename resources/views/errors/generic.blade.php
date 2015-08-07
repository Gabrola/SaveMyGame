@extends('layout')

@section('title', 'Error')

@section('content')
    <div class="card-panel red">
        <span class="white-text">
            @if ($errors->has())
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            @endif
        </span>
    </div>
@endsection