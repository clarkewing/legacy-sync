<?php

namespace ClarkeWing\LegacySync\Facades;

use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\LegacySyncManager;
use ClarkeWing\LegacySync\Testing\Fakes\LegacySyncFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ClarkeWing\LegacySync\LegacySyncManager syncAll(SyncDirection $direction)
 * @method static \ClarkeWing\LegacySync\LegacySyncManager syncTable(string $table, SyncDirection $direction)
 * @method static \ClarkeWing\LegacySync\LegacySyncManager syncRecord(string $table, int|string $recordKey, SyncDirection $direction)
 *
 * @see \ClarkeWing\LegacySync\LegacySyncManager
 */
class LegacySync extends Facade
{
    public static function fake(): LegacySyncFake
    {
        static::swap($fake = new LegacySyncFake);

        return $fake;
    }

    public static function isFake(): bool
    {
        return static::getFacadeRoot() instanceof LegacySyncFake;
    }

    protected static function getFacadeAccessor(): string
    {
        return LegacySyncManager::class;
    }
}
