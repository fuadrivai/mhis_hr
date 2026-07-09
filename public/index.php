<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$start = microtime(true);

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$request = Request::capture();

$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);

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
