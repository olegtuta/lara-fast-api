<?php

namespace Qsiq\RobotApi\Infrastructure\Robots;

use Illuminate\Contracts\Container\Container;
use Qsiq\RobotApi\Domain\Robots\RobotModule;
use Qsiq\RobotApi\Domain\Robots\RobotModuleRegistry;

final class ConfigRobotModuleRegistry implements RobotModuleRegistry
{
    /** @var array<string, RobotModule> */
    private array $modules = [];

    public function __construct(Container $container, array $robotsConfig)
    {
        foreach ($robotsConfig as $name => $config) {
            if (!is_string($name) || $name === '') {
                continue;
            }

            $moduleClass = $config['module'] ?? null;

            if (!is_string($moduleClass) || $moduleClass === '') {
                continue;
            }

            $module = $container->make($moduleClass, [
                'name' => $name,
                'config' => is_array($config) ? $config : [],
            ]);

            if ($module instanceof RobotModule) {
                $this->modules[strtolower($module->name())] = $module;
            }
        }
    }

    public function for(string $robot): ?RobotModule
    {
        $key = strtolower($robot);

        return $this->modules[$key] ?? null;
    }
}
