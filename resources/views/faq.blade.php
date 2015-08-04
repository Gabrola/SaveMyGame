@extends('layout')

@section('title', 'Frequently Asked Questions')

@section('content')
    <ul class="collapsible" data-collapsible="accordion">
        <li>
            <div class="collapsible-header">What does SaveMyGame do?</div>
            <div class="collapsible-body"><p>SaveMyGame will automatically record all your matches and it's statistics for you to watch at any time.</p></div>
        </li>
        <li>
            <div class="collapsible-header">Do I need to install any software?</div>
            <div class="collapsible-body"><p>No we will record all your matches, and for you to watch them, you simply need to run a command.</p></div>
        </li>
    </ul>
@endsection