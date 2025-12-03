<?php

namespace Qsiq\RobotApi\Infrastructure\Metrics;

use Illuminate\Support\Facades\Redis;
use Qsiq\RobotApi\Domain\Metrics\ResponseTimeSnapshotRepository;

final class RedisResponseTimeSnapshotRepository implements ResponseTimeSnapshotRepository
{
    private readonly string $totalKeyPattern;
    private readonly string $countKeyPattern;

    public function __construct()
    {
        $metrics = config('robot-api.metrics');
        $this->totalKeyPattern = $metrics['total_time_key'];
        $this->countKeyPattern = $metrics['count_key'];
    }

    public function totalTime(string $robot): float
    {
        $key = $this->formatKey($this->totalKeyPattern, $this->normalizeRobot($robot));
        $value = Redis::get($key);

        return $value === null ? 0.0 : (float) $value;
    }

    public function count(string $robot): int
    {
        $key = $this->formatKey($this->countKeyPattern, $this->normalizeRobot($robot));
        $value = Redis::get($key);

        return $value === null ? 0 : (int) $value;
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
