# Legacy Sync

[![Code Quality](https://github.com/clarkewing/legacy-sync/actions/workflows/code-quality.yml/badge.svg)](https://github.com/clarkewing/legacy-sync/actions/workflows/code-quality.yml)
[![Tests](https://github.com/clarkewing/legacy-sync/actions/workflows/tests.yaml/badge.svg)](https://github.com/clarkewing/legacy-sync/actions/workflows/tests.yaml)

**Legacy Sync** is a lightweight Laravel package for bi-directional database syncing between a legacy and modern app. It’s ideal for projects maintaining both a legacy and a modern Laravel app, and ensures data consistency across both systems without friction.

---

## 🚀 Features

- 🔄 Bi-directional database syncing between legacy and modern applications
- 🗄️ Flexible table and column mapping configuration
- 🧩 Support for different database column names and structures
- ⚙️ Customizable field defaults and exclusions
- 🌍 Shared, environment-agnostic logic — install on both apps
- 🔍 Efficient processing with lazy loading for large datasets

---

## 🧩 Requirements

To use Legacy Sync, the package must be installed in both Laravel applications you wish to sync.

Legacy Sync is compatible with applications using Laravel 8 and above, and requires PHP 8.1 or above.

---

## 📦 Installation

**Legacy Sync** must be installed in both your legacy app and the modern app.

```bash
composer require clarkewing/legacy-sync
```

Make sure you configure your database connections in your application’s `config/database.php` file:

```php
// config/database.php
'connections' => [
    // Your default connection...
    
    'legacy' => [
        'driver' => 'mysql',
        'host' => env('DB_LEGACY_HOST', 'localhost'),
        'port' => env('DB_LEGACY_PORT', '3306'),
        'database' => env('DB_LEGACY_DATABASE', 'legacy_db'),
        'username' => env('DB_LEGACY_USERNAME', 'root'),
        'password' => env('DB_LEGACY_PASSWORD', ''),
        'charset' => 'utf8mb4',
        // ...other database configuration options
    ],
],
```

---

## ⚙️ Configuration

Publish the config file using the following command:

```bash
php artisan vendor:publish --tag=legacy-sync-config
```

This will create a `config/legacy_sync.php` file with the following structure:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Database connections
    |--------------------------------------------------------------------------
    |
    | Here you should specify the connections used for syncing.
    | These should reference connection names defined in your config/database.php file.
    |
    */

    'connections' => [
        'legacy' => 'legacy', // References the 'legacy' connection in database.php
        'new' => 'sqlite',    // References the 'sqlite' connection in database.php
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
```

---

## 🔧 Usage

### Syncing Tables

Legacy Sync provides a simple API for syncing tables between your legacy and modern applications:

```php
use ClarkeWing\LegacySync\Facades\LegacySync;
use ClarkeWing\LegacySync\Enums\SyncDirection;

// Sync a specific table from legacy to new
LegacySync::syncTable('users', SyncDirection::LegacyToNew);

// Sync a specific table from new to legacy
LegacySync::syncTable('users', SyncDirection::NewToLegacy);

// Sync all configured tables from legacy to new
LegacySync::syncAll(SyncDirection::LegacyToNew);

// Sync all configured tables from new to legacy
LegacySync::syncAll(SyncDirection::NewToLegacy);
```

### Syncing Individual Records

For more granular control, you can sync individual records by their primary key:

```php
use ClarkeWing\LegacySync\Facades\LegacySync;
use ClarkeWing\LegacySync\Enums\SyncDirection;

// Sync a specific user with ID 123 from legacy to new
LegacySync::syncRecord('users', 123, SyncDirection::LegacyToNew);

// Sync a specific user with ID 456 from new to legacy
LegacySync::syncRecord('users', 456, SyncDirection::NewToLegacy);
```

### Artisan Commands

Legacy Sync also provides an Artisan command for syncing tables:

```bash
# Sync all tables from legacy to new
php artisan legacy:sync legacy_to_new

# Sync all tables from new to legacy
php artisan legacy:sync new_to_legacy

# Sync a specific table from legacy to new
php artisan legacy:sync legacy_to_new --table=users

# Sync a specific table from new to legacy
php artisan legacy:sync new_to_legacy --table=users
```

### Configuration Examples

#### Basic Column Mapping

When column names differ between legacy and new databases:

```php
'users' => [
    'primary_key' => 'id',
    'map' => [
        'legacy_column_name' => 'new_column_name',
        'user_name' => 'username',
        'birth_date' => 'birthdate',
        'email_address' => 'email',
    ],
],
```

#### Setting Default Values

For columns that exist in only one database:

```php
'users' => [
    'primary_key' => 'id',
    'map' => [
        'user_name' => 'username',
    ],
    'defaults' => [
        'active' => true,
        'verified' => false,
    ],
],
```

#### Excluding Columns

To exclude specific columns from syncing:

```php
'users' => [
    'primary_key' => 'id',
    'exclude' => [
        'legacy' => ['password_hash', 'remember_token'],
        'new' => ['password', 'two_factor_secret'],
    ],
],
```

---

## 🧪 Testing

You can safely prevent real database syncing during tests by faking the facade. When faked, all sync methods become no-ops and no database writes will occur.

- `LegacySync::fake()` swaps the facade to a fake implementation.
- `LegacySync::isFake()` lets you detect if the facade is currently faked.

Example (Pest):

```php
use ClarkeWing\LegacySync\Enums\SyncDirection;
use ClarkeWing\LegacySync\Facades\LegacySync;

it('does not perform real syncing when faked', function () {
    // Prevent any actual syncing work
    LegacySync::fake();

    expect(LegacySync::isFake())->toBeTrue();

    // These calls are intercepted and do nothing
    LegacySync::syncAll(SyncDirection::LegacyToNew);
    LegacySync::syncTable('users', SyncDirection::NewToLegacy);
    LegacySync::syncRecord('users', 123, SyncDirection::LegacyToNew);
});
```

You can also fake before invoking your own application code or Artisan commands that trigger syncing:

```php
use ClarkeWing\LegacySync\Facades\LegacySync;

LegacySync::fake();

// For example, a feature test that triggers a sync via a command
$this->artisan('legacy:sync legacy_to_new')
    ->assertSuccessful();
```

Note: The provided fake is intentionally a safe no-op and does not record calls for assertions. If you need to assert specific interactions, mock your own collaborators or assert application-side effects instead of database changes.

---

## 🔐 Security

When syncing sensitive data between databases, ensure both systems have appropriate security measures in place. Consider excluding sensitive fields from syncing or implementing additional encryption as needed.

---

## 🤝 Contributing

Issues and PRs welcome! Please see our [Contribution guidelines](CONTRIBUTING.md) if contributing tests or features.

---

## 📜 License

Released under the MIT License.

---
