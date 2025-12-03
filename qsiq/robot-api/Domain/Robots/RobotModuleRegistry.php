<?php

namespace Qsiq\RobotApi\Domain\Robots;

interface RobotModuleRegistry
{
    public function for(string $robot): ?RobotModule;
}
