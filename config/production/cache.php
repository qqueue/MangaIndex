<?php

return [

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

    'driver' => 'memcached',

    'memcached' => [
        ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100],
    ],

    'prefix' => 'mangaindex_prod',

];