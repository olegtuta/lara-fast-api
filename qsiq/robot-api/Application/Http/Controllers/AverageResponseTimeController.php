<?php

namespace Qsiq\RobotApi\Application\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Qsiq\RobotApi\Domain\Robots\RobotModuleRegistry;

final class AverageResponseTimeController
{
    public function __construct(private readonly RobotModuleRegistry $modules)
    {
    }

    public function __invoke(string $robot): JsonResponse
    {
        $module = $this->modules->for($robot);

        if ($module === null) {
            return response()->json(['message' => 'Robot not found.'], 404);
        }

        $snapshots = $module->responseTimeSnapshots();

        $count = $snapshots->count($module->name());
        $totalSeconds = $snapshots->totalTime($module->name());
        $average = $count === 0 ? 0.0 : $totalSeconds / $count;

        return response()->json([
            'robot' => $module->name(),
            'count' => $count,
            'total_response_time_seconds' => round($totalSeconds, 6),
            'average_response_time_seconds' => round($average, 6),
        ]);
    }
}
