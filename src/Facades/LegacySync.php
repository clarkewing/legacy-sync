<?php

namespace ClarkeWing\LegacySync\Facades;

use ClarkeWing\LegacySync\LegacySyncManager;
use ClarkeWing\LegacySync\Testing\Fakes\LegacySyncFake;
use Illuminate\Support\Facades\Facade;

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
