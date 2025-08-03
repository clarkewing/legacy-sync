<?php

namespace ClarkeWing\LegacySync\Actions\Traits;

use ClarkeWing\LegacySync\Actions\MapSyncableRecord;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

trait SyncsRecords
{
    protected string $primaryKey;

    protected string $sourceConnection;

    protected string $targetConnection;

    protected string $table;

    protected SyncDirection $direction;

    /**
     * @param  array<string, mixed>|object  $record
     */
    protected function syncRecord(array|object $record): void
    {
        if (is_object($record)) {
            /** @var array<string, mixed> $record */
            $record = (array) $record;
        }

        $mapped = (new MapSyncableRecord)->handle($record, $this->table, $this->direction);

        if (! isset($mapped[$this->primaryKey])) {
            throw new RuntimeException("Missing primary key [$this->primaryKey] in mapped record for table [$this->table]");
        }

        DB::connection($this->targetConnection)
            ->table($this->table)
            ->updateOrInsert([$this->primaryKey => $mapped[$this->primaryKey]], $mapped);
    }

    protected function setUp(string $table, SyncDirection $direction): void
    {
        /**
         * @var array{
         *     primary_key: string,
         *     map?: array<string, string>,
         *     defaults?: array<string, mixed>,
         *     exclude?: array<string, string[]>
         * } $tableConfig
         */
        $tableConfig = config("legacy_sync.mapping.{$table}");

        if (! $tableConfig || ! isset($tableConfig['primary_key'])) {
            throw new InvalidArgumentException("Missing mapping or primary_key for table '{$table}'");
        }

        $this->primaryKey = $tableConfig['primary_key'];

        /** @var array<string, string> $connectionsConfig */
        $connectionsConfig = config('legacy_sync.connections');
        $this->sourceConnection = $connectionsConfig[$direction->sourceKey()];
        $this->targetConnection = $connectionsConfig[$direction->targetKey()];

        $this->table = $table;
        $this->direction = $direction;
    }
}
