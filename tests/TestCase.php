<?php

namespace VanOns\LaravelAttachmentLibrary\Test;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\GlideServiceProvider;
use VanOns\LaravelAttachmentLibrary\LaravelAttachmentLibraryServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * Load package service provider.
     *
     * @param  $app  \Illuminate\Foundation\Application
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelAttachmentLibraryServiceProvider::class,
            GlideServiceProvider::class,
        ];
    }

    /**
     * Load package alias.
     *
     * @param  $app  \Illuminate\Foundation\Application
     */
    protected function getPackageAliases($app): array
    {
        return [
            'AttachmentManager' => AttachmentManager::class,
        ];
    }

    /**
     * The `web` middleware group requires an encryption key.
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }

    protected function afterRefreshingDatabase(): void
    {
        $migrationsPath = __DIR__ . '/../database/migrations';

        $migrations = [
            require "{$migrationsPath}/create_attachments_table.php.stub",
            require "{$migrationsPath}/create_attachables_table.php.stub",
            require "{$migrationsPath}/add_collection_to_attachables_table.php.stub",
            require "{$migrationsPath}/add_order_to_attachables_table.php.stub",
            require "{$migrationsPath}/add_focal_point_to_attachments_table.php.stub",
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }
    }
}
