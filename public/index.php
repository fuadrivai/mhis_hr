<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
$start = LARAVEL_START;

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$request = Request::capture();

$app->handleRequest($request);

// Catat waktu request
file_put_contents(
    storage_path('logs/request_time.log'),
    sprintf(
        "[%s] %s %s : %.2f ms\n",
        date('Y-m-d H:i:s'),
        $request->method(),
        $request->path(),
        (microtime(true) - $start) * 1000
    ),
    FILE_APPEND
);
