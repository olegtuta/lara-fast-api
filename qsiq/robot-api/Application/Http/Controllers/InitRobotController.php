<?php

namespace Qsiq\RobotApi\Application\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Qsiq\RobotApi\Application\Services\FetchRobotInitData;

final class InitRobotController
{
    public function __construct(private readonly FetchRobotInitData $fetchRobotInitData)
    {
    }

    public function __invoke(string $robot, string $bot_id): JsonResponse
    {
        $snapshot = $this->fetchRobotInitData->handle($robot, $bot_id);

        if ($snapshot === null) {
            return response()->json(['message' => 'Bot data not found.'], 404);
        }

        return response()->json($snapshot->toArray(), 200);
    }
}
