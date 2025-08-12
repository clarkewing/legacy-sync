<?php

namespace ClarkeWing\LegacySync\Testing\Fakes;

use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\LegacySyncManager;

class LegacySyncFake extends LegacySyncManager
{
    public function syncAll(SyncDirection $direction): void
    {
        //
    }

    public function syncTable(string $table, SyncDirection $direction): void
    {
        //
    }

    public function syncRecord(string $table, int|string $recordKey, SyncDirection $direction): void
    {
        //
    }
}
