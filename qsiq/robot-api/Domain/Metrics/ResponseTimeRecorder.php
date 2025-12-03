<?php

namespace Qsiq\RobotApi\Domain\Metrics;

interface ResponseTimeRecorder
{
    public function record(string $robot, float $seconds): void;
}
