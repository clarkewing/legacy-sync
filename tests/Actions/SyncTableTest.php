<?php

use ClarkeWing\LegacySync\Actions\SyncTable;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;

test('handle syncs all records from legacy to new', function () {
    // Insert test data in legacy
    DB::connection('legacy')->table('users')->insert([
        ['id' => 1, 'email' => 'user1@example.com', 'birthdate' => '1990-01-01'],
        ['id' => 2, 'email' => 'user2@example.com', 'birthdate' => '1991-02-02'],
        ['id' => 3, 'email' => 'user3@example.com', 'birthdate' => '1992-03-03'],
    ]);

    // Sync the table
    (new SyncTable)->handle('users', SyncDirection::LegacyToNew);

    // Check if all records were synced to new database
    $newRecords = DB::connection('sqlite')->table('users')->orderBy('id')->get();

    expect($newRecords)->toHaveCount(3)
        ->and($newRecords[0])
        ->id->toBe(1)
        ->email->toBe('user1@example.com')
        ->birth_date->toBe('1990-01-01')
        ->and($newRecords[1])
        ->id->toBe(2)
        ->email->toBe('user2@example.com')
        ->birth_date->toBe('1991-02-02')
        ->and($newRecords[2])
        ->id->toBe(3)
        ->email->toBe('user3@example.com')
        ->birth_date->toBe('1992-03-03');

});

test('handle syncs all records from new to legacy', function () {
    // Insert test data in new
    DB::connection('sqlite')->table('users')->insert([
        ['id' => 4, 'email' => 'user4@example.com', 'birth_date' => '1993-04-04'],
        ['id' => 5, 'email' => 'user5@example.com', 'birth_date' => '1994-05-05'],
    ]);

    // Sync the table
    (new SyncTable)->handle('users', SyncDirection::NewToLegacy);

    // Check if all records were synced to legacy database
    $legacyRecords = DB::connection('legacy')->table('users')->orderBy('id')->get();

    expect($legacyRecords)->toHaveCount(2)
        ->and($legacyRecords[0])
        ->id->toBe(4)
        ->email->toBe('user4@example.com')
        ->birthdate->toBe('1993-04-04')
        ->and($legacyRecords[1])
        ->id->toBe(5)
        ->email->toBe('user5@example.com')
        ->birthdate->toBe('1994-05-05');

});

test('handle updates existing records in target database', function () {
    // Insert initial data in both databases
    DB::connection('legacy')->table('users')->insert([
        ['id' => 10, 'email' => 'original1@example.com', 'birthdate' => '1980-01-01'],
        ['id' => 11, 'email' => 'original2@example.com', 'birthdate' => '1981-02-02'],
    ]);

    DB::connection('sqlite')->table('users')->insert([
        ['id' => 10, 'email' => 'outdated1@example.com', 'birth_date' => '1980-12-31'],
        ['id' => 11, 'email' => 'outdated2@example.com', 'birth_date' => '1981-12-31'],
    ]);

    // Sync the table
    (new SyncTable)->handle('users', SyncDirection::LegacyToNew);

    // Check if records were updated in new database
    $newRecords = DB::connection('sqlite')->table('users')->orderBy('id')->get();

    expect($newRecords)->toHaveCount(2)
        ->and($newRecords[0])
        ->id->toBe(10)
        ->email->toBe('original1@example.com')
        ->birth_date->toBe('1980-01-01')
        ->and($newRecords[1])
        ->id->toBe(11)
        ->email->toBe('original2@example.com')
        ->birth_date->toBe('1981-02-02');

});

test('handle throws exception when table mapping is missing', function () {
    expect(fn () => (new SyncTable)->handle('missing_table', SyncDirection::LegacyToNew))
        ->toThrow(InvalidArgumentException::class, "Missing mapping or primary_key for table 'missing_table'");
});

test('handle throws exception when primary key is missing in mapped record', function () {
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

    expect(fn () => (new SyncTable)->handle('users', SyncDirection::LegacyToNew))
        ->toThrow(RuntimeException::class, 'Missing primary key [missing_id] in mapped record for table [users]');
});

test('handle works with empty source table', function () {
    // No data in source table

    // Sync the table
    (new SyncTable)->handle('users', SyncDirection::LegacyToNew);

    // Check that no records were synced
    $newRecords = DB::connection('sqlite')->table('users')->get();

    expect($newRecords)->toHaveCount(0);
});
