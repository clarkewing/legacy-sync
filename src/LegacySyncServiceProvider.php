<?php

namespace ClarkeWing\LegacySync;

use ClarkeWing\LegacySync\Commands\SyncLegacyData;
use Illuminate\Support\ServiceProvider;

class LegacySyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerPackageConfig();
    }

    public function boot(): void
    {
        $this->publishPackageConfig();

        $this->registerArtisanCommands();
    }

    protected function registerPackageConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/legacy_sync.php', 'legacy_sync'
        );
    }

    protected function publishPackageConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/legacy_sync.php' => config_path('legacy_sync.php'),
        ], 'legacy-sync-config');
    }

    protected function registerArtisanCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncLegacyData::class,
            ]);
        }
    }
}
