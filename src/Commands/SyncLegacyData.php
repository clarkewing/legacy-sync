<?php

namespace ClarkeWing\LegacySync\Commands;

use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\LegacySyncManager;
use Illuminate\Console\Command;

class SyncLegacyData extends Command
{
    protected $signature = 'legacy:sync {direction=legacy_to_new : The sync direction (legacy_to_new or new_to_legacy)}
                            {--table= : Name of table to sync (optional - by default all configured tables are synced)}';

    protected $description = 'Sync legacy and new databases based on configured mappings';

    public function handle(): void
    {
        /** @var string $directionArg */
        $directionArg = $this->argument('direction');

        $direction = match ($directionArg) {
            'legacy_to_new' => SyncDirection::LegacyToNew,
            'new_to_legacy' => SyncDirection::NewToLegacy,
            default => throw new \InvalidArgumentException("Invalid direction: '$directionArg'"),
        };

        $this->info("Starting sync: $direction->name");

        if ($table = $this->option('table')) { /** @var string $table */
            app(LegacySyncManager::class)->syncTable($table, $direction);
        } else {
            app(LegacySyncManager::class)->syncAll($direction);
        }

        $this->info('Sync complete.');
    }
}
