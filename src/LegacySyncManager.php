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

    /**
     * @return string[]
     */
    protected function getSyncableTables(): array
    {
        /**
         * @var array<string, array{
         *     primary_key: string,
         *     map?: array<string, string>,
         *     defaults?: array<string, mixed>,
         *     exclude?: array<string, string[]>
         * }> $mappingConfig
         */
        $mappingConfig = config('legacy_sync.mapping', []);

        return array_keys($mappingConfig);
    }
}
