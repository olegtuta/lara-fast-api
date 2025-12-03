<?php

namespace Qsiq\RobotApi;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Qsiq\RobotApi\Application\Http\Middleware\TrackResponseTime;
use Qsiq\RobotApi\Application\Http\Middleware\VerifyRobotApiKey;
use Qsiq\RobotApi\Application\Services\FetchRobotInitData;
use Qsiq\RobotApi\Domain\Robots\RobotModuleRegistry;
use Qsiq\RobotApi\Infrastructure\Robots\ConfigRobotModuleRegistry;

class RobotApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/robot-api.php', 'robot-api');

        $this->app->singleton(RobotModuleRegistry::class, function ($app) {
            $robots = config('robot-api.robots', []);

            return new ConfigRobotModuleRegistry($app, is_array($robots) ? $robots : []);
        });

        $this->app->singleton(FetchRobotInitData::class, function ($app) {
            return new FetchRobotInitData($app->make(RobotModuleRegistry::class));
        });
    }

    public function boot(): void
    {
        Route::pattern('robot', '[a-z0-9_-]+');

            Route::middleware('api')->group(function (): void {
                Route::prefix('api/{robot}/qsiqkz')->group(__DIR__ . '/routes/api.php');
                Route::prefix('api/{robot}/qsiq')->group(__DIR__ . '/routes/api.php');
            });

        $this->app['router']->aliasMiddleware('robot.response_time', TrackResponseTime::class);
        $this->app['router']->aliasMiddleware('robot.verify_api_key', VerifyRobotApiKey::class);
    }
}
