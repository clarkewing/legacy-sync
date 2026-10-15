<?php

use ClarkeWing\LegacySync\Actions\DeleteRecord;
use ClarkeWing\LegacySync\Enums\SyncDirection;
use Illuminate\Support\Facades\DB;

test('handle deletes record from legacy', function () {
    DB::connection('legacy')->table('users')->insert([
        ['id' => 10, 'email' => 'deleted@example.com', 'birthdate' => '1995-05-05'],
        ['id' => 11, 'email' => 'kept@example.com', 'birthdate' => '1995-05-05'],
    ]);

    (new DeleteRecord)->handle('users', 10, SyncDirection::NewToLegacy);

    expect(DB::connection('legacy')->table('users')->pluck('id')->all())->toBe([11]);
});

test('handle deletes record from new', function () {
    DB::connection('sqlite')->table('users')->insert([
        ['id' => 20, 'email' => 'deleted@example.com', 'birth_date' => '2000-10-10'],
        ['id' => 21, 'email' => 'kept@example.com', 'birth_date' => '2000-10-10'],
    ]);

    (new DeleteRecord)->handle('users', 20, SyncDirection::LegacyToNew);

    expect(DB::connection('sqlite')->table('users')->pluck('id')->all())->toBe([21]);
});

test('handle does nothing when record is already missing from target', function () {
    DB::connection('legacy')->table('users')->insert([
        'id' => 30,
        'email' => 'kept@example.com',
        'birthdate' => '1990-01-01',
    ]);

    (new DeleteRecord)->handle('users', 999, SyncDirection::NewToLegacy);

    expect(DB::connection('legacy')->table('users')->pluck('id')->all())->toBe([30]);
});

test('handle throws exception when table mapping is missing', function () {
    expect(fn () => (new DeleteRecord)->handle('missing_table', 1, SyncDirection::NewToLegacy))
        ->toThrow(InvalidArgumentException::class, "Missing mapping or primary_key for table 'missing_table'");
});
