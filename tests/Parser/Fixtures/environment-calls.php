<?php

declare(strict_types=1);

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;

return [
    'laravel-specific-calls' => [
        'env' => env('ENV'),
        'env-double-quotes' => env("ENV_DOUBLE_QUOTES"),
        'env-with-default' => env('ENV_DEFAULT', 'default'),

        'Env::get' => Env::get('EnvGet'),
        'Env::get-double-quotes' => Env::get("EnvGetDoubleQuotes"),
        'Env::get-with-default' => Env::get('EnvGetDefault', 'default'),

        'Illuminate\Support\Env' => Illuminate\Support\Env::get('Illuminate\Support\Env'),
        'Illuminate\Support\Env-double-quotes' => Illuminate\Support\Env::get("Illuminate\Support\EnvDoubleQuotes"),
        'Illuminate\Support\Env-with-default' => Illuminate\Support\Env::get('Illuminate\Support\EnvDefault', 'default'),

        '\Illuminate\Support\Env' => \Illuminate\Support\Env::get('\Illuminate\Support\Env'),
        '\Illuminate\Support\Env-double-quotes' => \Illuminate\Support\Env::get("\Illuminate\Support\EnvDoubleQuotes"),
        '\Illuminate\Support\Env-with-default' => \Illuminate\Support\Env::get('\Illuminate\Support\EnvDefault', 'default'),

        'Config::get' => Config::get('StrAfter'),
        'Config::get-with-default' => Config::get('StrAfter', 'Str'),
    ],

    'php-calls' => [
        'getenv' => getenv('GETENV'),
        'getenv-double-quotes' => getenv("GETENV_DOUBLE_QUOTES"),
        'getenv-with-local_only' => getenv('GETENV_DEFAULT', true),

        '$_ENV' => $_ENV['$_ENV'],
        '$_ENV-double-quotes' => $_ENV["\$_ENV_DOUBLE_QUOTES"],
        '$_ENV-with-default-broken' => $_ENV['$_ENV_DEFAULT', 'asd'],
        '$_ENV-with-null-coalescing-default' => $_ENV['$_ENV_NULL_COALESCING_DEFAULT'] ?? 'default', // Not null-coalescing default not supported yet?

        // Not supported
        '$_SERVER' => $_SERVER['$_SERVER'],
        '$_SERVER-double-quotes' => $_SERVER["\$_SERVER_DOUBLE_QUOTES"],
        '$_SERVER-with-default' => $_SERVER['$_SERVER_DEFAULT'] ?? 'default',
    ],
];
