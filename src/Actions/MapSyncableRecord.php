<?php

namespace ClarkeWing\LegacySync\Actions;

use ClarkeWing\LegacySync\Enums\SyncDirection;

final class MapSyncableRecord
{
    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public static function fromLegacy(array $record, string $table): array
    {
        return (new self)->handle($record, $table, SyncDirection::LegacyToNew);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public static function toLegacy(array $record, string $table): array
    {
        return (new self)->handle($record, $table, SyncDirection::NewToLegacy);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function handle(array $record, string $table, SyncDirection $direction): array
    {
        /**
         * @var array{
         *     primary_key: string,
         *     map?: array<string, string>,
         *     defaults?: array<string, mixed>,
         *     exclude?: array<string, string[]>
         * } $tableConfig
         */
        $tableConfig = config("legacy_sync.mapping.{$table}", []);
        $rawMap = $tableConfig['map'] ?? [];
        $defaults = $tableConfig['defaults'] ?? [];
        $exclude = $tableConfig['exclude'][$direction->targetKey()] ?? [];

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
