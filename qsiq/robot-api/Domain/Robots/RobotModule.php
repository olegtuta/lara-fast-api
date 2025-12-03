<?php

namespace Qsiq\RobotApi\Domain\Robots;

use Qsiq\RobotApi\Domain\Lead\RobotLeadRepository;
use Qsiq\RobotApi\Domain\Metrics\ResponseTimeRecorder;
use Qsiq\RobotApi\Domain\Metrics\ResponseTimeSnapshotRepository;

interface RobotModule
{
    public function name(): string;

    public function leadRepository(): RobotLeadRepository;

    public function responseTimeRecorder(): ResponseTimeRecorder;

    public function responseTimeSnapshots(): ResponseTimeSnapshotRepository;
}
