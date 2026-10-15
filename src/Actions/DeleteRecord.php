<?php

namespace ClarkeWing\LegacySync\Actions;

use ClarkeWing\LegacySync\Actions\Traits\SyncsRecords;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;

class DeleteRecord
{
    use SyncsRecords;

    /**
     * Delete the record from the direction's target, typically after it was
     * deleted from the source. A record already missing from the target is
     * left alone.
     */
    public function handle(string $table, int|string $recordKey, SyncDirection $direction): void
    {
        $this->setUp($table, $direction);

        DB::connection($this->targetConnection)
            ->table($this->table)
            ->where($this->primaryKey, $recordKey)
            ->delete();
    }
}
