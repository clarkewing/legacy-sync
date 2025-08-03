<?php

namespace ClarkeWing\LegacySync\Actions;

use ClarkeWing\LegacySync\Actions\Traits\SyncsRecords;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class SyncTable
{
    use SyncsRecords;

    public function handle(string $table, SyncDirection $direction): void
    {
        $this->setUp($table, $direction);

        $this->getSourceRecords()
            ->each(fn (array|object $record, int|string $key): null => $this->syncRecord($record));
    }

    /**
     * @return LazyCollection<int, object>
     */
    protected function getSourceRecords(): LazyCollection
    {
        return DB::connection($this->sourceConnection)
            ->table($this->table)
            ->orderBy($this->primaryKey)
            ->lazy();
    }
}
