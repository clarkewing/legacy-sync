<?php

namespace ClarkeWing\LegacySync\Tests;

use ClarkeWing\LegacySync\LegacySyncServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LegacySyncServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        Model::preventLazyLoading();

        $app['config']->set('app.key', 'base64:XKIF+krFyL/DetvgSFCnmAOUn99navUB2AeeMbDIbcM=');

        $app['config']->set('database.connections.legacy', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase($app): void
    {
        $legacySchema = $app['db']->connection('legacy')->getSchemaBuilder();
        $legacySchema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('birthdate');
        });

        $newSchema = $app['db']->connection()->getSchemaBuilder();
        $newSchema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('birth_date');
        });
    }
}
