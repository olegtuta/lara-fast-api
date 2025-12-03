<?php

namespace Qsiq\RobotApi\Infrastructure\Robots\Mia;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Qsiq\RobotApi\Domain\Lead\RobotLeadRepository;
use Qsiq\RobotApi\Domain\Lead\RobotLeadSnapshot;
use Throwable;

final class MiaRobotLeadRepository implements RobotLeadRepository
{
    /**
     * @param array{phone:string,name:string,license_sheet:string,sum_ap:string,address:string} $columns
     */
    public function __construct(
        private readonly string $virtualConnection,
        private readonly string $smartDialConnection,
        private readonly string $botTable,
        private readonly string $leadColumn,
        private readonly string $queueColumn,
        private readonly string $modBaseTable,
        private readonly string $customTablePrefix,
        private readonly array $columns,
    ) {
    }

    public function findByBotId(string $botId): ?RobotLeadSnapshot
    {
        $botRow = DB::connection($this->virtualConnection)->selectOne(
            sprintf(
                'SELECT %1$s AS lead_id, %2$s AS queue_id FROM %3$s WHERE id = :bot_id LIMIT 1',
                $this->leadColumn,
                $this->queueColumn,
                $this->botTable,
            ),
            ['bot_id' => $botId],
        );

        if ($botRow === null) {
            return null;
        }

        $leadId = $botRow->lead_id ?? null;
        $queueId = $botRow->queue_id ?? null;

        if ($leadId === null || $queueId === null) {
            return null;
        }

        $modBase = DB::connection($this->smartDialConnection)->selectOne(
            sprintf('SELECT * FROM %s WHERE lead_id = :lead_id LIMIT 1', $this->modBaseTable),
            ['lead_id' => $leadId],
        );

        $customRow = $this->fetchCustomRow($queueId, (string) $leadId);

        return new RobotLeadSnapshot(
            $this->value($modBase, $this->columns['phone']),
            $this->value($customRow, $this->columns['name']) ?? $this->value($modBase, $this->columns['name']),
            $this->value($customRow, $this->columns['license_sheet']),
            $this->value($customRow, $this->columns['sum_ap']),
            $this->value($customRow, $this->columns['address']) ?? $this->value($modBase, $this->columns['address']),
        );
    }

    private function fetchCustomRow(string|int $queueId, string $leadId): ?object
    {
        $sanitizedQueueId = preg_replace('/[^0-9a-zA-Z_]/', '', (string) $queueId);

        if ($sanitizedQueueId === '') {
            return null;
        }

        $tableName = $this->customTablePrefix . $sanitizedQueueId;

        if (!$this->customTableExists($tableName)) {
            return null;
        }

        try {
            return DB::connection($this->smartDialConnection)->selectOne(
                sprintf('SELECT * FROM %s WHERE lead_id = :lead_id LIMIT 1', $tableName),
                ['lead_id' => $leadId],
            );
        } catch (QueryException) {
            return null;
        }
    }

    private function customTableExists(string $tableName): bool
    {
        $cacheKey = 'robot_api:mia:custom_table_exists:' . $tableName;

        return Cache::rememberForever($cacheKey, function () use ($tableName): bool {
            [$database, $table] = $this->splitTableName($tableName);
            if ($table === '') {
                return false;
            }

            try {
                $connection = DB::connection($this->smartDialConnection);
                if ($database === null || $database === '') {
                    $database = (string) $connection->getDatabaseName();
                }
                if ($database === '') {
                    return false;
                }

                $result = $connection->selectOne(
                    'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table LIMIT 1',
                    [
                        'schema' => $database,
                        'table' => $table,
                    ],
                );

                return $result !== null;
            } catch (Throwable) {
                return false;
            }
        });
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function splitTableName(string $tableName): array
    {
        $parts = explode('.', $tableName, 2);

        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        return [null, $parts[0]];
    }

    private function value(?object $row, string $column): ?string
    {
        if ($row === null || $column === '') {
            return null;
        }

        if (!property_exists($row, $column)) {
            return null;
        }

        $value = $row->{$column};

        return $value === null ? null : (string) $value;
    }
}
