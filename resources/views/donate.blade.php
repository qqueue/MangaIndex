@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a> Donate
    </h1>
@stop


@section('main')
    <div class="container">
        <p>
            If you like this site and would like to help support it, please consider donating via one of the things below.
        </p>

        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="KUZGX59GLPFJ2">
            <button class="button-text" name="submit" value="">PayPal</button>
        </form>
    </div>
@stop
