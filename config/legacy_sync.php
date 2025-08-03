<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database connections
    |--------------------------------------------------------------------------
    |
    | Here you should specify the connections used for syncing.
    |
    */

    'connections' => [
        'legacy' => 'legacy',
        'new' => 'sqlite',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy database sync mapping
    |--------------------------------------------------------------------------
    |
    | Here you should specify the mapping and defaults used for syncing
    | the legacy and new databases of your app.
    | Expected format:
    | 'table_name' => [
    |    'map' => [
    |        'legacy_column_name' => 'new_column_name',
    |        // Columns which share the same name in databases are implicitly mapped 1:1.
    |        // One-sided fields which aren't explicitly mapped and don't have a default are omitted from the sync.
    |    ],
    |    // Defaults for optional fields that exist only in the new or legacy table
    |    'defaults' => [
    |        'reputation' => 0,
    |    ],
    |    // Exclude fields that don't exist in one database to avoid errors
    |    'exclude' => [
    |        'legacy' => ['missing_from_legacy'],
    |        'new' => ['missing_from_new'],
    |    ],
    |
    */

    'mapping' => [
        'users' => [
            'primary_key' => 'id',

            'map' => [
                'birthdate' => 'birth_date',
            ],
        ],
    ],
];
