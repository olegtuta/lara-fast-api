<?php

namespace Tests\Feature;

use Qsiq\RobotApi\Domain\Robots\RobotModule;
use Qsiq\RobotApi\Domain\Robots\RobotModuleRegistry;
use Tests\TestCase;

class MiaRouteAvailabilityTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function endpointProvider(): array
    {
        return [
            'base namespace' => ['/api/mia/qsiq/123456/init'],
            'kazakhstan namespace' => ['/api/mia/qsiqkz/123456/init'],
        ];
    }

    /**
     * @dataProvider endpointProvider
     */
    public function test_routes_are_registered(string $endpoint): void
    {
        config(['robot-api.api_key' => 'testing-key']);

        $this->mockRegistryReturning(null);

        $response = $this->getJson($endpoint, ['x-api-key' => 'testing-key']);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Bot data not found.']);
    }

    private function mockRegistryReturning(?RobotModule $module): void
    {
        $registry = new class($module) implements RobotModuleRegistry {
            public function __construct(private readonly ?RobotModule $module)
            {
            }

            public function for(string $robot): ?RobotModule
            {
                return $this->module;
            }
        };

        $this->app->instance(RobotModuleRegistry::class, $registry);
    }
}
