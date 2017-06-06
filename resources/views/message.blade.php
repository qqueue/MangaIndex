@extends('layout')

@section('pageHeading')
    <h1>
        <a href="/">/</a>
        {{ $title }}
    </h1>
@stop

@section('main')
    @parent

    <div class="container">
        <p>
            {{ $message }}
        </p>
    </div>
@stop