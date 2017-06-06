<?php

App::before(function ($request) {
    if (Request::has('minify')) {
        if (Request::get('minify')) {
            Minify::enable();
        } else {
            Minify::disable();
        }
    }
});


App::after(function ($request, $response) {
    //
});

// verify _token request param to match token in session
Route::filter('csrf', function () {
    if (Session::token() != Request::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});

// require logged in user
Route::filter('auth', function () {
    if (!Auth::check()) {
        abort(403, 'You are not logged in');
    }
});

// require user to have superuser perms
Route::filter('auth.super', function () {
    $user = Auth::user();
    if (!$user) {
        abort(403, 'You are not logged in');
    } elseif (!$user->hasSuper()) {
        abort(403, 'You don\'t have permission to do that');
    }
});
