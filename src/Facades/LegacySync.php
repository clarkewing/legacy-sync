<?php

namespace ClarkeWing\LegacySync\Facades;

use ClarkeWing\LegacySync\LegacySyncManager;
use ClarkeWing\LegacySync\Testing\Fakes\LegacySyncFake;
use Illuminate\Support\Facades\Facade;

class LegacySync extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LegacySyncManager::class;
    }
}
