<?php

use ClarkeWing\LegacySync\Actions\SyncTable;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\LegacySyncManager;
use Mockery\MockInterface;

beforeEach(function () {
    config()->set('legacy_sync.mapping', [
        'users' => [
            'primary_key' => 'id',
            'map' => [
                'birthdate' => 'birth_date',
            ],
        ],
        'posts' => [
            'primary_key' => 'id',
            'map' => [],
        ],
    ]);
});

test('getSyncableTables returns array of table names from config', function () {
    expect(invade(new LegacySyncManager)->getSyncableTables())
        ->toBe(['users', 'posts']);
});

test('getSyncableTables returns empty array when no mappings exist', function () {
    // Override config with empty mapping
    config()->set('legacy_sync.mapping', []);

    expect(invade(new LegacySyncManager)->getSyncableTables())
        ->toBeArray()
        ->toBeEmpty();
});

test('syncTable calls SyncTable action with correct parameters', function () {
    // Mock the SyncTable action
    $this->mock(SyncTable::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')
            ->once()
            ->with('users', SyncDirection::LegacyToNew)
            ->andReturn(null);
    });

    (new LegacySyncManager)->syncTable('users', SyncDirection::LegacyToNew);
});

test('syncAll calls syncTable for each table in config', function () {
    $manager = $this->partialMock(LegacySyncManager::class, function (MockInterface $mock) {
        $mock->shouldReceive('syncTable')
            ->once()
            ->with('users', SyncDirection::NewToLegacy);

        $mock->shouldReceive('syncTable')
            ->once()
            ->with('posts', SyncDirection::NewToLegacy);

        // Allow getSyncableTables to run normally
        $mock->shouldAllowMockingProtectedMethods()
            ->makePartial();
    });

    $manager->syncAll(SyncDirection::NewToLegacy);
});

test('syncAll does nothing when no tables are configured', function () {
    // Override config with empty mapping
    config()->set('legacy_sync.mapping', []);

    $manager = $this->partialMock(LegacySyncManager::class, function (MockInterface $mock) {
        $mock->shouldReceive('syncTable')
            ->never();

        // Allow getSyncableTables to run normally
        $mock->shouldAllowMockingProtectedMethods()
            ->makePartial();
    });

    $manager->syncAll(SyncDirection::LegacyToNew);
});

test('syncTable handles both sync directions correctly', function () {
    // Test LegacyToNew direction
    $this->mock(SyncTable::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')
            ->once()
            ->with('users', SyncDirection::LegacyToNew)
            ->andReturn(null);
    });

    (new LegacySyncManager)->syncTable('users', SyncDirection::LegacyToNew);

    // Reset mock and test NewToLegacy direction
    $this->mock(SyncTable::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')
            ->once()
            ->with('users', SyncDirection::NewToLegacy)
            ->andReturn(null);
    });

    (new LegacySyncManager)->syncTable('users', SyncDirection::NewToLegacy);
});
