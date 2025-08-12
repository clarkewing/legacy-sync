<?php

use ClarkeWing\LegacySync\Actions\SyncRecord;
use ClarkeWing\LegacySync\Actions\SyncTable;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\Facades\LegacySync;
use Mockery\MockInterface;

it('resolves to the LegacySyncManager by default and delegates calls', function () {
    // Mock the SyncTable action and ensure it gets called via the facade
    $this->mock(SyncTable::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')
            ->once()
            ->with('users', SyncDirection::LegacyToNew)
            ->andReturnNull();
    });

    // Call via facade
    LegacySync::syncTable('users', SyncDirection::LegacyToNew);

    // Mock the SyncRecord action and ensure it gets called via the facade
    $this->mock(SyncRecord::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')
            ->once()
            ->with('users', 1, SyncDirection::LegacyToNew)
            ->andReturnNull();
    });

    // Call via facade
    LegacySync::syncRecord('users', 1, SyncDirection::LegacyToNew);
});
