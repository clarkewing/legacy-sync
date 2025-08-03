<?php

namespace ClarkeWing\LegacySync\Actions;

use ClarkeWing\LegacySync\Actions\Traits\SyncsRecords;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SyncRecord
{
    use SyncsRecords {
        setUp as traitSetUp;
    }

    protected int|string $recordKey;

    public function handle(string $table, int|string $recordKey, SyncDirection $direction): void
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

    protected function setUp(string $table, int|string $recordKey, SyncDirection $direction): void
    {
        $this->traitSetUp($table, $direction);

        $this->recordKey = $recordKey;
    }
}
