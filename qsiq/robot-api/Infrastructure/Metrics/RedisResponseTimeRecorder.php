<?php

namespace Qsiq\RobotApi\Infrastructure\Metrics;

use Illuminate\Support\Facades\Redis;
use Qsiq\RobotApi\Domain\Metrics\ResponseTimeRecorder;

final class RedisResponseTimeRecorder implements ResponseTimeRecorder
{
    private readonly string $totalKeyPattern;
    private readonly string $countKeyPattern;

    public function __construct()
    {
        $metrics = config('robot-api.metrics');
        $this->totalKeyPattern = $metrics['total_time_key'];
        $this->countKeyPattern = $metrics['count_key'];
    }

    public function record(string $robot, float $seconds): void
    {
        $seconds = max(0.0, $seconds);

        $robotKey = $this->normalizeRobot($robot);
        $totalKey = $this->formatKey($this->totalKeyPattern, $robotKey);
        $countKey = $this->formatKey($this->countKeyPattern, $robotKey);

        Redis::connection()->pipeline(function ($pipe) use ($seconds, $totalKey, $countKey): void {
            $pipe->incrbyfloat($totalKey, $seconds);
            $pipe->incr($countKey);
        });
    }

    private function normalizeRobot(string $robot): string
    {
        $normalized = strtolower($robot);
        $normalized = preg_replace('/[^a-z0-9_-]/', '', $normalized) ?? '';

        if ($normalized === '') {
            $normalized = config('robot-api.default_robot', 'mia');
        }

        return $normalized;
    }

    private function formatKey(string $pattern, string $robot): string
    {
        return str_contains($pattern, '%s')
            ? sprintf($pattern, $robot)
            : rtrim($pattern, ':') . ':' . $robot;
    }
}
