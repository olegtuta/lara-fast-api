<?php

namespace Qsiq\RobotApi\Domain\Metrics;

interface ResponseTimeSnapshotRepository
{
    public function totalTime(string $robot): float;

    public function count(string $robot): int;
}
