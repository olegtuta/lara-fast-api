<?php

namespace Qsiq\RobotApi\Infrastructure\Robots\Mia;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Qsiq\RobotApi\Domain\Lead\RobotLeadRepository;
use Qsiq\RobotApi\Domain\Metrics\ResponseTimeRecorder;
use Qsiq\RobotApi\Domain\Metrics\ResponseTimeSnapshotRepository;
use Qsiq\RobotApi\Domain\Robots\RobotModule;
use Qsiq\RobotApi\Infrastructure\Metrics\RedisResponseTimeRecorder;
use Qsiq\RobotApi\Infrastructure\Metrics\RedisResponseTimeSnapshotRepository;

final class MiaRobotModule implements RobotModule
{
    private readonly string $name;

    private ?RobotLeadRepository $leadRepository = null;
    private ?ResponseTimeRecorder $responseTimeRecorder = null;
    private ?ResponseTimeSnapshotRepository $responseTimeSnapshotRepository = null;

    public function __construct(
        string $name,
        private readonly array $config,
        private readonly Container $container,
    ) {
        $filtered = preg_replace('/[^0-9a-zA-Z_-]/', '', $name) ?? '';
        $filtered = strtolower($filtered);
        $this->name = $filtered === '' ? 'mia' : $filtered;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function leadRepository(): RobotLeadRepository
    {
        if ($this->leadRepository === null) {
            $connections = $this->config['connections'] ?? [];
            $useKzConnections = $this->isKzRequest();

            $virtualConnection = $useKzConnections
                ? ($connections['virtual_kz'] ?? 'mysql_virtual_kz')
                : ($connections['virtual'] ?? 'mysql_virtual');

            $smartDialConnection = $useKzConnections
                ? ($connections['smart_dial_kz'] ?? 'mysql_smart_dial_kz')
                : ($connections['smart_dial'] ?? 'mysql_smart_dial');

            $tables = $this->config['tables'] ?? [];
            $columns = $this->config['columns'] ?? [];

            $this->leadRepository = new MiaRobotLeadRepository(
                $virtualConnection,
                $smartDialConnection,
                $this->sanitizeTable($tables['bot'] ?? 'virtual.mia_bots', 'virtual.mia_bots'),
                $this->sanitizeColumn($columns['lead'] ?? 'lead_id', 'lead_id'),
                $this->sanitizeColumn($columns['queue'] ?? 'queue_id', 'queue_id'),
                $this->sanitizeTable($tables['mod_base'] ?? 'smart_dial.mod_base', 'smart_dial.mod_base'),
                $this->sanitizeTablePrefix($tables['custom_prefix'] ?? 'smart_dial.custom_'),
                [
                    'phone' => $this->sanitizeColumn($columns['phone'] ?? 'phone', 'phone'),
                    'name' => $this->sanitizeColumn($columns['name'] ?? 'name', 'name'),
                    'license_sheet' => $this->sanitizeColumn($columns['license_sheet'] ?? 'lic_scheet', 'lic_scheet'),
                    'sum_ap' => $this->sanitizeColumn($columns['sum_ap'] ?? 'sum_ap', 'sum_ap'),
                    'address' => $this->sanitizeColumn($columns['address'] ?? 'addr', 'addr'),
                ],
            );
        }

        return $this->leadRepository;
    }

    public function responseTimeRecorder(): ResponseTimeRecorder
    {
        if ($this->responseTimeRecorder === null) {
            $this->responseTimeRecorder = $this->container->make(RedisResponseTimeRecorder::class);
        }

        return $this->responseTimeRecorder;
    }

    public function responseTimeSnapshots(): ResponseTimeSnapshotRepository
    {
        if ($this->responseTimeSnapshotRepository === null) {
            $this->responseTimeSnapshotRepository = $this->container->make(RedisResponseTimeSnapshotRepository::class);
        }

        return $this->responseTimeSnapshotRepository;
    }

    private function sanitizeTable(?string $table, string $fallback): string
    {
        if (!is_string($table) || $table === '') {
            return $fallback;
        }

        $sanitized = preg_replace('/[^0-9a-zA-Z_\.]/', '', $table) ?? '';

        return $sanitized === '' ? $fallback : $sanitized;
    }

    private function sanitizeColumn(?string $column, string $fallback): string
    {
        if (!is_string($column) || $column === '') {
            return $fallback;
        }

        $sanitized = preg_replace('/[^0-9a-zA-Z_]/', '', $column) ?? '';

        return $sanitized === '' ? $fallback : $sanitized;
    }

    private function sanitizeTablePrefix(?string $prefix): string
    {
        if (!is_string($prefix) || $prefix === '') {
            return 'smart_dial.custom_';
        }

        $sanitized = preg_replace('/[^0-9a-zA-Z_\.]/', '', $prefix) ?? '';

        $sanitized = $sanitized === '' ? 'smart_dial.custom_' : $sanitized;

        return str_ends_with($sanitized, '_') ? $sanitized : $sanitized . '_';
    }

    private function isKzRequest(): bool
    {
        if (!$this->container->bound('request')) {
            return false;
        }

        $request = $this->container->make('request');

        if (!$request instanceof Request) {
            return false;
        }

        $path = $request->path();

        if (!is_string($path) || $path === '') {
            return false;
        }

        return str_contains($path, 'qsiqkz');
    }
}
