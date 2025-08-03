<?php

namespace ClarkeWing\LegacySync\Actions;

use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class SyncRecord
{
    protected string $primaryKey;

    protected string $sourceConnection;

    protected string $targetConnection;

    protected string $table;

    protected mixed $recordKey;

    protected SyncDirection $direction;

    public function handle(string $table, mixed $recordKey, SyncDirection $direction): void
    {
        $this->setUp($table, $recordKey, $direction);

        if (! $record = $this->getSourceRecord()) {
            throw new RuntimeException("Record [$this->recordKey] not found in table [$this->table] on [$this->sourceConnection]");
        }

        $this->syncRecord($record);
    }

    protected function getSourceRecord(): ?object
    {
        return DB::connection($this->sourceConnection)
            ->table($this->table)
            ->where($this->primaryKey, $this->recordKey)
            ->first();
    }

    protected function syncRecord(array|object $record): void
    {
        if (is_object($record)) {
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

    protected function setUp(string $table, mixed $recordKey, SyncDirection $direction): void
    {
        $tableConfig = config("legacy_sync.mapping.{$table}");

        if (! $tableConfig || ! isset($tableConfig['primary_key'])) {
            throw new InvalidArgumentException("Missing mapping or primary_key for table '{$table}'");
        }

        $this->primaryKey = $tableConfig['primary_key'];

        $this->sourceConnection = config('legacy_sync.connections.'.$direction->sourceKey());
        $this->targetConnection = config('legacy_sync.connections.'.$direction->targetKey());

        $this->table = $table;
        $this->recordKey = $recordKey;
        $this->direction = $direction;
    }
}
