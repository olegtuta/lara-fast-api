<?php

namespace Qsiq\RobotApi\Application\Services;

use Qsiq\RobotApi\Domain\Lead\RobotLeadSnapshot;
use Qsiq\RobotApi\Domain\Robots\RobotModuleRegistry;

final class FetchRobotInitData
{
    public function __construct(private readonly RobotModuleRegistry $modules)
    {
    }

    public function handle(string $robot, string $botId): ?RobotLeadSnapshot
    {
        $module = $this->modules->for($robot);

        if ($module === null) {
            return null;
        }

        return $module->leadRepository()->findByBotId($botId);
    }
}
