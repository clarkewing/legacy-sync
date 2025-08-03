<?php

use ClarkeWing\LegacySync\Actions\MapSyncableRecord;
use ClarkeWing\LegacySync\Enums\SyncDirection;

beforeEach(function () {
    config()->set('legacy_sync.mapping', [
        'test_table' => [
            'primary_key' => 'id',
            'map' => [
                'legacy_field' => 'new_field',
                'shared_field' => 'shared_field',
            ],
            'defaults' => [
                'default_field' => 'default value',
            ],
            'exclude' => [
                'legacy' => ['missing_from_legacy'],
                'new' => ['missing_from_new'],
            ],
        ],
    ]);
});

test('fromLegacy maps record from legacy to new format', function () {
    $record = [
        'id' => 1,
        'legacy_field' => 'test value',
        'shared_field' => 'shared value',
        'missing_from_new' => 'should be excluded',
    ];

    $result = MapSyncableRecord::fromLegacy($record, 'test_table');

    expect($result)
        ->toBeArray()
        ->toHaveKey('id', 1)
        ->toHaveKey('new_field', 'test value')
        ->toHaveKey('shared_field', 'shared value')
        ->not->toHaveKey('missing_from_new')
        ->toHaveKey('default_field', 'default value');
});

test('toLegacy maps record from new to legacy format', function () {
    $record = [
        'id' => 1,
        'new_field' => 'test value',
        'shared_field' => 'shared value',
        'missing_from_legacy' => 'should be excluded',
    ];

    $result = MapSyncableRecord::toLegacy($record, 'test_table');

    expect($result)
        ->toBeArray()
        ->toHaveKey('id', 1)
        ->toHaveKey('legacy_field', 'test value')
        ->toHaveKey('shared_field', 'shared value')
        ->not->toHaveKey('missing_from_legacy')
        ->toHaveKey('default_field', 'default value');
});

test('handle applies mapping correctly for LegacyToNew direction', function () {
    $record = [
        'id' => 1,
        'legacy_field' => 'test value',
        'shared_field' => 'shared value',
    ];

    $result = (new MapSyncableRecord)->handle($record, 'test_table', SyncDirection::LegacyToNew);

    expect($result)
        ->toBeArray()
        ->toHaveKey('id', 1)
        ->toHaveKey('new_field', 'test value')
        ->toHaveKey('shared_field', 'shared value')
        ->toHaveKey('default_field', 'default value');
});

test('handle applies mapping correctly for NewToLegacy direction', function () {
    $record = [
        'id' => 1,
        'new_field' => 'test value',
        'shared_field' => 'shared value',
    ];

    $result = (new MapSyncableRecord)->handle($record, 'test_table', SyncDirection::NewToLegacy);

    expect($result)
        ->toBeArray()
        ->toHaveKey('id', 1)
        ->toHaveKey('legacy_field', 'test value')
        ->toHaveKey('shared_field', 'shared value')
        ->toHaveKey('default_field', 'default value');
});

test('handle respects exclusion lists', function () {
    $record = [
        'id' => 1,
        'legacy_field' => 'test value',
        'missing_from_new' => 'should be excluded',
    ];

    $result = (new MapSyncableRecord)->handle($record, 'test_table', SyncDirection::LegacyToNew);

    expect($result)
        ->toBeArray()
        ->not->toHaveKey('missing_from_new');

    $record = [
        'id' => 1,
        'new_field' => 'test value',
        'missing_from_legacy' => 'should be excluded',
    ];

    $result = (new MapSyncableRecord)->handle($record, 'test_table', SyncDirection::NewToLegacy);

    expect($result)
        ->toBeArray()
        ->not->toHaveKey('missing_from_legacy');
});

test('handle works with empty mapping configuration', function () {
    config()->set('legacy_sync.mapping.empty_table', []);

    $record = [
        'id' => 1,
        'field' => 'value',
    ];

    $result = (new MapSyncableRecord)->handle($record, 'empty_table', SyncDirection::LegacyToNew);

    expect($result)
        ->toBeArray()
        ->toHaveKey('id', 1)
        ->toHaveKey('field', 'value');
});

test('handle works with non-existent table configuration', function () {
    $record = [
        'id' => 1,
        'field' => 'value',
    ];

    $result = (new MapSyncableRecord)->handle($record, 'non_existent_table', SyncDirection::LegacyToNew);

    expect($result)
        ->toBeArray()
        ->toHaveKey('id', 1)
        ->toHaveKey('field', 'value');
});
