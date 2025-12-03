<?php

namespace Qsiq\RobotApi\Application\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyRobotApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $configuredKey = config('robot-api.api_key');

        if ($configuredKey === null) {
            return $next($request);
        }

        $providedKey = $request->header('x-api-key');

        if (!is_string($providedKey) || !hash_equals($configuredKey, $providedKey)) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
