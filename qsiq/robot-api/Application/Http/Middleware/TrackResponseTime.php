<?php

namespace Qsiq\RobotApi\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Qsiq\RobotApi\Domain\Robots\RobotModuleRegistry;

final class TrackResponseTime
{
    public function __construct(private readonly RobotModuleRegistry $modules)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        try {
            return $next($request);
        } finally {
            $robot = $this->resolveRobotFromRequest($request);
            $module = $this->modules->for($robot);

            if ($module !== null) {
                $module->responseTimeRecorder()->record($module->name(), max(0.0, microtime(true) - $start));
            }
        }
    }

    private function resolveRobotFromRequest(Request $request): string
    {
        $route = $request->route();
        $robot = null;

        if (is_object($route) && method_exists($route, 'parameter')) {
            $robot = $route->parameter('robot');
        } elseif (is_array($route) && array_key_exists('robot', $route)) {
            $robot = $route['robot'];
        }

        if (!is_string($robot) || $robot === '') {
            $robot = config('robot-api.default_robot', 'mia');
        }

        return $robot;
    }
}
