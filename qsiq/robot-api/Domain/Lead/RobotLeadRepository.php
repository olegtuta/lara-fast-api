<?php

namespace Qsiq\RobotApi\Domain\Lead;

interface RobotLeadRepository
{
    public function findByBotId(string $botId): ?RobotLeadSnapshot;
}
