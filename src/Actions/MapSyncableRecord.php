<?php

namespace ClarkeWing\LegacySync\Actions;

use ClarkeWing\LegacySync\Enums\SyncDirection;

class MapSyncableRecord
{
    public static function fromLegacy(array $record, string $table): array
    {
        return (new static)->handle($record, $table, SyncDirection::LegacyToNew);
    }

    public static function toLegacy(array $record, string $table): array
    {
        return (new static)->handle($record, $table, SyncDirection::NewToLegacy);
    }

    public function handle(array $record, string $table, SyncDirection $direction): array
    {
        $config = config("legacy_sync.mapping.{$table}", []);
        $rawMap = $config['map'] ?? [];
        $defaults = $config['defaults'] ?? [];
        $exclude = $config['exclude'][$direction->targetKey()] ?? [];

        $map = match ($direction) {
            SyncDirection::LegacyToNew => $rawMap,
            SyncDirection::NewToLegacy => array_flip($rawMap)
        };

        $output = [];

        foreach ($record as $key => $value) {
            $targetKey = $map[$key] ?? $key;

            if (in_array($targetKey, $exclude, true)) {
                continue;
            }

            $output[$targetKey] = $value;
        }

        foreach ($defaults as $key => $value) {
            if (! in_array($key, $exclude, true)) {
                $output[$key] ??= $value;
            }
        }

        return $output;
    }
}
