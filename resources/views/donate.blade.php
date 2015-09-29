@extends('layout')

@section('title', 'Donations')

@section('content')
    <div class="row">
        <div class="col col-xs-12">
            <p class="flow-text">
                Our server currently costs $68.88 USD per month so it would be awesome if you could help me with some of these costs.
                I am not looking to profit from this at all, so I'm keeping the website ad-free for a clean and unobtrusive experience.
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col m6 center-align">
            <h4>Paypal</h4>
            <p class="flow-text">Send any paypal payments to admin@savemyga.me</p>
        </div>
        <div class="col m6 center-align">
            <h4>Bitcoin</h4>
            <p class="flow-text">
                <a href="bitcoin:3F2e8UpbEqXT6gxoekqHEE61myaXyt1qd3">3F2e8UpbEqXT6gxoekqHEE61myaXyt1qd3</a><br>
                <img src="https://chart.googleapis.com/chart?chs=275x275&cht=qr&chl=bitcoin:3F2e8UpbEqXT6gxoekqHEE61myaXyt1qd3&chld=L|2" alt="3F2e8UpbEqXT6gxoekqHEE61myaXyt1qd3"/>
            </p>
        </div>
    </div>
@endsection