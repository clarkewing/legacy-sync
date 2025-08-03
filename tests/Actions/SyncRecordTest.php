<?php

use ClarkeWing\LegacySync\Actions\SyncRecord;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;

test('handle syncs record from legacy to new', function () {
    // Insert test data in legacy
    DB::connection('legacy')->table('users')->insert([
        'id' => 10,
        'email' => 'test@example.com',
        'birthdate' => '1995-05-05',
    ]);

    // Sync the record
    (new SyncRecord)->handle('users', 10, SyncDirection::LegacyToNew);

    // Check if record was synced to new database
    $newRecord = DB::connection('sqlite')->table('users')->where('id', 10)->first();

    expect($newRecord)->not->toBeNull()
        ->and($newRecord)
        ->id->toBe(10)
        ->email->toBe('test@example.com')
        ->birth_date->toBe('1995-05-05');
});

test('handle syncs record from new to legacy', function () {
    // Insert test data in new
    DB::connection('sqlite')->table('users')->insert([
        'id' => 20,
        'email' => 'new@example.com',
        'birth_date' => '2000-10-10',
    ]);

    // Sync the record
    (new SyncRecord)->handle('users', 20, SyncDirection::NewToLegacy);

    // Check if record was synced to legacy database
    $legacyRecord = DB::connection('legacy')->table('users')->where('id', 20)->first();

    expect($legacyRecord)->not->toBeNull()
        ->and($legacyRecord)
        ->id->toBe(20)
        ->email->toBe('new@example.com')
        ->birthdate->toBe('2000-10-10');
});

test('handle throws exception when record not found', function () {
    expect(fn () => (new SyncRecord)->handle('users', 999, SyncDirection::LegacyToNew))
        ->toThrow(RuntimeException::class, 'Record [999] not found in table [users] on [legacy]');
});

test('handle throws exception when missing primary key in mapped record', function () {
    // Set up config without primary key in mapping
    config()->set('legacy_sync.mapping.users', [
        'primary_key' => 'missing_id',
        'map' => [],
    ]);

    // Insert test data
    DB::connection('legacy')->table('users')->insert([
        'id' => 30,
        'email' => 'broken@example.com',
        'birthdate' => '1990-01-01',
    ]);

    expect(fn () => (new SyncRecord)->handle('users', 30, SyncDirection::LegacyToNew))
        ->toThrow(RuntimeException::class, 'Record [30] not found in table [users] on [legacy]');
});

test('handle throws exception when table mapping is missing', function () {
    expect(fn () => (new SyncRecord)->handle('missing_table', 1, SyncDirection::LegacyToNew))
        ->toThrow(InvalidArgumentException::class, "Missing mapping or primary_key for table 'missing_table'");
});

test('handle updates existing record in target database', function () {
    // Insert initial data in both databases
    DB::connection('legacy')->table('users')->insert([
        'id' => 40,
        'email' => 'original@example.com',
        'birthdate' => '1980-01-01',
    ]);

    DB::connection('sqlite')->table('users')->insert([
        'id' => 40,
        'email' => 'outdated@example.com',
        'birth_date' => '1980-12-31',
    ]);

    // Sync the record
    (new SyncRecord)->handle('users', 40, SyncDirection::LegacyToNew);

    // Check if record was updated in new database
    $updatedRecord = DB::connection('sqlite')->table('users')->where('id', 40)->first();

    expect($updatedRecord)->not->toBeNull()
        ->and($updatedRecord)
        ->id->toBe(40)
        ->email->toBe('original@example.com')
        ->birth_date->toBe('1980-01-01');
});
