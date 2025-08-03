<?php

namespace ClarkeWing\LegacySync\Enums;

enum SyncDirection
{
    case LegacyToNew;

    case NewToLegacy;

    public function sourceKey(): string
    {
        return match ($this) {
            SyncDirection::LegacyToNew => 'legacy',
            SyncDirection::NewToLegacy => 'new',
        };
    }

    public function targetKey(): string
    {
        return match ($this) {
            SyncDirection::LegacyToNew => 'new',
            SyncDirection::NewToLegacy => 'legacy',
        };
    }
}
