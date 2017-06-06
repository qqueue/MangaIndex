<?php
/*
| Does any of this need to be marged over into AppServiceProvider?
| Not familiar enough with Laravel... seems not?
*/




/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories([

    app_path().'/commands',
    app_path().'/controllers',
    app_path().'/models',
    app_path().'/observers',
    app_path().'/composers',
    app_path().'/database/seeds',
    app_path().'/lib',

]);

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useDailyFiles(storage_path().'/logs/log');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function (Exception $exception, $code) {
    $logParams = [];

    foreach (['REQUEST_URI', 'HTTP_REFERER'] as $key) {
        if (array_key_exists($key, $_SERVER)) {
            $logParams[$key] = $_SERVER[$key];
        }
    }

    if ($code !== 404) { // don't bother logging 404s
        Log::error($exception, $logParams);
    }

    if (Config::get('app.debug') === false) {
        $message = $exception->getMessage();
        $title = $code;

        if (!empty($message)) {
            $title = 'Error';

            if (method_exists($exception, 'getStatusCode')) {
                $title = $exception->getStatusCode();
            }
        } elseif ($exception instanceof Illuminate\Session\TokenMismatchException) {
            $message = 'Your session token is invalid. Please go back and try again.';
        } else {
            // if no message was specified then try and use one from a pre-defined list of HTTP errors
            $codeMessages = [
                403 => 'Access denied',
                404 => 'The requested resource was not found'
            ];

            if (array_key_exists($code, $codeMessages)) {
                $message = $codeMessages[$code];
            } else {
                // most likely is a server error if we've reached this far...
                $message = 'Server error';
            }
        }


        if (Request::ajax()) {
            return Response::json(['result' => false, 'message' => $message], $code);
        } else {
            return Response::view('message', ['title' => $title, 'message' => $message], $code);
        }
    }
});

/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function () {
    return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';

// view composers
require app_path().'/composers.php';
