@extends('layout')

@section('body')
    <div id="reader" data-path="{{ $path }}" data-files="{{ $files }}" data-index="{{ $index }}">
        <div id="reader-page">
            <img src="" alt />
        </div>
    </div>
@stop