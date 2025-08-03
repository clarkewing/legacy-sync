<?php

namespace ClarkeWing\LegacySync;

use ClarkeWing\LegacySync\Actions\SyncTable;
use ClarkeWing\LegacySync\Enums\SyncDirection;

class LegacySyncManager
{
    public function syncAll(SyncDirection $direction): void
    {
        foreach ($this->getSyncableTables() as $table) {
            $this->syncTable($table, $direction);
        }
    }

    public function syncTable(string $table, SyncDirection $direction): void
    {
        resolve(SyncTable::class)->handle($table, $direction);
    }

    protected function getSyncableTables(): array
    {
        return array_keys(config('legacy_sync.mapping', []));
    }
}
