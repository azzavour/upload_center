<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Debugbar Enable
    |--------------------------------------------------------------------------
    |
    | Control if the debugbar is enabled. We default to the DEBUGBAR_ENABLED
    | flag, but when that isn't set we only enable during local development
    | while APP_DEBUG is true. Production environments therefore disable it.
    |
    */

    'enabled' => env('DEBUGBAR_ENABLED', env('APP_ENV') === 'local' && env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Storage settings
    |--------------------------------------------------------------------------
    |
    | Debugbar stores data for AJAX requests. You can disable this, so the debugbar
    | stores data only for the current request.
    |
    */

    'storage' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => storage_path('debugbar'),
        'connection' => null,
        'provider' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendors
    |--------------------------------------------------------------------------
    |
    | Enable / disable vendors: edge case when you want to use specific app CSS/JS.
    |
    */

    'include_vendors' => true,

    /*
    |--------------------------------------------------------------------------
    | Capture Ajax Requests
    |--------------------------------------------------------------------------
    */

    'capture_ajax' => true,

    /*
    |--------------------------------------------------------------------------
    | Collectors
    |--------------------------------------------------------------------------
    */

    'collectors' => [
        'phpinfo' => true,
        'messages' => true,
        'time' => true,
        'memory' => true,
        'exceptions' => true,
        'log' => true,
        'db' => true,
        'views' => true,
        'route' => true,
        'auth' => true,
        'gate' => true,
        'session' => true,
        'symfony_request' => true,
        'mail' => true,
        'laravel' => true,
        'events' => false,
        'default_request' => false,
        'logs' => false,
        'files' => false,
        'config' => false,
        'cache' => false,
        'models' => false,
        'livewire' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    */

    'options' => [
        'auth' => [
            'show_name' => true,
        ],
        'db' => [
            'with_params' => true,
            'backtrace' => true,
            'timeline' => true,
            'duration_background' => true,
        ],
        'mail' => [
            'full_log' => false,
        ],
        'views' => [
            'data' => false,
        ],
        'route' => [
            'label' => true,
        ],
        'logs' => [
            'file' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Inject Debugbar in Response
    |--------------------------------------------------------------------------
    */

    'inject' => true,

    /*
    |--------------------------------------------------------------------------
    | Remote
    |--------------------------------------------------------------------------
    |
    | When enabled, allows collecting the data from different sources, instead
    | of the current request only. Works in combination with the debugbar client.
    |
    */

    'remote' => false,

];
