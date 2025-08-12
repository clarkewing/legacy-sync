<?php

use ClarkeWing\LegacySync\Actions\SyncRecord;
use ClarkeWing\LegacySync\Actions\SyncTable;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\Facades\LegacySync;
use ClarkeWing\LegacySync\LegacySyncManager;
use ClarkeWing\LegacySync\Testing\Fakes\LegacySyncFake;
use Mockery\MockInterface;

it('resolves to the LegacySyncManager by default and delegates calls', function () {
    // Ensure facade is not faked initially
    expect(LegacySync::isFake())->toBeFalse();

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

it('can be faked via LegacySync::fake() which prevents real syncing', function () {
    // When faked, the SyncTable and SyncRecord actions should never be invoked
    $this->mock(SyncTable::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->never();
    });
    $this->mock(SyncRecord::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->never();
    });

    $fake = LegacySync::fake();

    expect($fake)->toBeInstanceOf(LegacySyncFake::class)
        ->and(LegacySync::isFake())->toBeTrue();

    // Calls should be intercepted by the fake and do nothing
    LegacySync::syncRecord('users', 1, SyncDirection::NewToLegacy);
    LegacySync::syncTable('users', SyncDirection::NewToLegacy);
    LegacySync::syncAll(SyncDirection::LegacyToNew);
});

it('continues to resolve from the container when not faked', function () {
    // Reset the container binding explicitly to ensure a real instance is available
    $this->app->forgetInstance(LegacySyncManager::class);

    // Ensure the container gives us a real manager
    $resolved = $this->app->make(LegacySyncManager::class);
    expect($resolved)->toBeInstanceOf(LegacySyncManager::class);
});
