<?php

namespace Minify;

use Config;

class MinifyServiceProvider extends \CeesVanEgmond\Minify\MinifyServiceProvider
{

    public function register()
    {
        $this->app['Minify'] = $this->app->share(function ($app) {
            return new Minify(
                [
                    'css_build_path' => config('minify::css_build_path'),
                    'js_build_path' => config('minify::js_build_path'),
                    'ignore_environments' => config('minify::ignore_environments'),
                    'base_url' => config('minify::base_url'),
                ],
                $app->environment()
            );
        });
    }
}
