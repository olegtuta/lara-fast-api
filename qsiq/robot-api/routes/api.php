<?php

use Illuminate\Support\Facades\Route;
use Qsiq\RobotApi\Application\Http\Controllers\AverageResponseTimeController;
use Qsiq\RobotApi\Application\Http\Controllers\InitRobotController;

Route::middleware(['robot.verify_api_key', 'robot.response_time'])
    ->get('{bot_id}/init', InitRobotController::class)
    ->where('bot_id', '[0-9A-Za-z_-]+');

Route::middleware('robot.verify_api_key')
    ->get('metrics/average-response-time', AverageResponseTimeController::class);
