<?php

return [

    /*
	|--------------------------------------------------------------------------
	| Default Cache Store
	|--------------------------------------------------------------------------
	|
	| This option controls the default cache connection that gets used while
	| using this caching library. This connection is used when another is
	| not explicitly specified when executing a given caching function.
	|
	*/

    'default' => env('CACHE_DRIVER', 'file'),

     /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */
    'stores' => [
        'apc' => [
            'driver' => 'apc',
        ],
        'array' => [
            'driver' => 'array',
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT  => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
    ],

    /*
	|--------------------------------------------------------------------------
	| Default Cache Driver
	|--------------------------------------------------------------------------
	|
	| This option controls the default cache "driver" that will be used when
	| using the Caching library. Of course, you may use other drivers any
	| time you wish. This is the default when another is not specified.
	|
	| Supported: "file", "database", "apc", "memcached", "redis", "array"
	|
	*/

    'driver' => 'array',

    /*
	|--------------------------------------------------------------------------
	| File Cache Location
	|--------------------------------------------------------------------------
	|
	| When using the "file" cache driver, we need a location where the cache
	| files may be stored. A sensible default has been specified, but you
	| are free to change it to any other place on disk that you desire.
	|
	*/

    'path' => storage_path().'/cache',

    /*
	|--------------------------------------------------------------------------
	| Database Cache Connection
	|--------------------------------------------------------------------------
	|
	| When using the "database" cache driver you may specify the connection
	| that should be used to store the cached items. When this option is
	| null the default database connection will be utilized for cache.
	|
	*/

    'connection' => null,

    /*
	|--------------------------------------------------------------------------
	| Database Cache Table
	|--------------------------------------------------------------------------
	|
	| When using the "database" cache driver we need to know the table that
	| should be used to store the cached items. A default table name has
	| been provided but you're free to change it however you deem fit.
	|
	*/

    'table' => 'cache',

    /*
	|--------------------------------------------------------------------------
	| Memcached Servers
	|--------------------------------------------------------------------------
	|
	| Now you may specify an array of your Memcached servers that should be
	| used when utilizing the Memcached cache driver. All of the servers
	| should contain a value for "host", "port", and "weight" options.
	|
	*/

    'memcached' => [

        ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100],

    ],

    /*
	|--------------------------------------------------------------------------
	| Cache Key Prefix
	|--------------------------------------------------------------------------
	|
	| When utilizing a RAM based store such as APC or Memcached, there might
	| be other applications utilizing the same cache. So, we'll specify a
	| value to get prefixed to all our keys so we can avoid collisions.
	|
	*/

    'prefix' => 'laravel',

];
