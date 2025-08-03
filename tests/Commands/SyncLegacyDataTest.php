<?php

use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\LegacySyncManager;
use Illuminate\Support\Facades\DB;

test('legacy:sync command syncs all tables with legacy_to_new direction', function () {
    // Mock the LegacySyncManager
    $manager = mock(LegacySyncManager::class);
    $manager->shouldReceive('syncAll')
        ->once()
        ->with(SyncDirection::LegacyToNew);

    // Replace the real manager with our mock
    $this->app->instance(LegacySyncManager::class, $manager);

    // Run the command
    $this->artisan('legacy:sync')
        ->expectsOutput('Starting sync: LegacyToNew')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();
});

test('legacy:sync command syncs all tables with new_to_legacy direction', function () {
    // Mock the LegacySyncManager
    $manager = mock(LegacySyncManager::class);
    $manager->shouldReceive('syncAll')
        ->once()
        ->with(SyncDirection::NewToLegacy);

    // Replace the real manager with our mock
    $this->app->instance(LegacySyncManager::class, $manager);

    // Run the command
    $this->artisan('legacy:sync', ['direction' => 'new_to_legacy'])
        ->expectsOutput('Starting sync: NewToLegacy')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();
});

test('legacy:sync command syncs specific table with legacy_to_new direction', function () {
    // Mock the LegacySyncManager
    $manager = mock(LegacySyncManager::class);
    $manager->shouldReceive('syncTable')
        ->once()
        ->with('users', SyncDirection::LegacyToNew);

    // Replace the real manager with our mock
    $this->app->instance(LegacySyncManager::class, $manager);

    // Run the command
    $this->artisan('legacy:sync', ['--table' => 'users'])
        ->expectsOutput('Starting sync: LegacyToNew')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();
});

test('legacy:sync command syncs specific table with new_to_legacy direction', function () {
    // Mock the LegacySyncManager
    $manager = mock(LegacySyncManager::class);
    $manager->shouldReceive('syncTable')
        ->once()
        ->with('users', SyncDirection::NewToLegacy);

    // Replace the real manager with our mock
    $this->app->instance(LegacySyncManager::class, $manager);

    // Run the command
    $this->artisan('legacy:sync', ['direction' => 'new_to_legacy', '--table' => 'users'])
        ->expectsOutput('Starting sync: NewToLegacy')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();
});

test('legacy:sync command throws exception for invalid direction', function () {
    expect(fn () => $this->artisan('legacy:sync', ['direction' => 'invalid_direction']))
        ->toThrow(InvalidArgumentException::class, 'Invalid direction: \'invalid_direction\'');
});

test('legacy:sync command performs actual sync from legacy to new', function () {
    // Insert test data in legacy
    DB::connection('legacy')->table('users')->insert([
        ['id' => 1, 'email' => 'user1@example.com', 'birthdate' => '1990-01-01'],
        ['id' => 2, 'email' => 'user2@example.com', 'birthdate' => '1991-02-02'],
    ]);

    // Run the command
    $this->artisan('legacy:sync')
        ->expectsOutput('Starting sync: LegacyToNew')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();

    // Check if records were synced to new database
    $newRecords = DB::connection('sqlite')->table('users')->orderBy('id')->get();

    expect($newRecords)->toHaveCount(2)
        ->and($newRecords[0])
        ->id->toBe(1)
        ->email->toBe('user1@example.com')
        ->birth_date->toBe('1990-01-01')
        ->and($newRecords[1])
        ->id->toBe(2)
        ->email->toBe('user2@example.com')
        ->birth_date->toBe('1991-02-02');
});

test('legacy:sync command performs actual sync from new to legacy', function () {
    // Insert test data in new
    DB::connection('sqlite')->table('users')->insert([
        ['id' => 3, 'email' => 'user3@example.com', 'birth_date' => '1992-03-03'],
        ['id' => 4, 'email' => 'user4@example.com', 'birth_date' => '1993-04-04'],
    ]);

    // Run the command
    $this->artisan('legacy:sync', ['direction' => 'new_to_legacy'])
        ->expectsOutput('Starting sync: NewToLegacy')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();

    // Check if records were synced to legacy database
    $legacyRecords = DB::connection('legacy')->table('users')->orderBy('id')->get();

    expect($legacyRecords)->toHaveCount(2)
        ->and($legacyRecords[0])
        ->id->toBe(3)
        ->email->toBe('user3@example.com')
        ->birthdate->toBe('1992-03-03')
        ->and($legacyRecords[1])
        ->id->toBe(4)
        ->email->toBe('user4@example.com')
        ->birthdate->toBe('1993-04-04');
});

test('legacy:sync command performs actual sync for specific table', function () {
    // Insert test data in legacy
    DB::connection('legacy')->table('users')->insert([
        ['id' => 5, 'email' => 'user5@example.com', 'birthdate' => '1994-05-05'],
    ]);

    // Run the command
    $this->artisan('legacy:sync', ['--table' => 'users'])
        ->expectsOutput('Starting sync: LegacyToNew')
        ->expectsOutput('Sync complete.')
        ->assertSuccessful();

    // Check if records were synced to new database
    $newRecords = DB::connection('sqlite')->table('users')->get();

    expect($newRecords)->toHaveCount(1)
        ->and($newRecords[0])
        ->id->toBe(5)
        ->email->toBe('user5@example.com')
        ->birth_date->toBe('1994-05-05');
});
