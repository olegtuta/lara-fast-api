<?php

use Illuminate\Support\Facades\Route;

Route::get('/healthz', function () {
    return response()->json(data: [
        'laravel' => app()->version(),
        'redis_host' => env('REDIS_HOST'),
        'db_host' => env('DB_HOST'),
    ]);
});
