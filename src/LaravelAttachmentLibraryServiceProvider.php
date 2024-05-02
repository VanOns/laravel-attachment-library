<?php

namespace VanOns\LaravelAttachmentLibrary;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAttachmentLibraryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-attachment-library')
            ->hasConfigFile()
            ->hasMigrations(['create_attachment_table', 'create_attachables_table'])
            ->runsMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile()
                    ->publishMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToRunMigrations();
            });
    }

    public function packageBooted(): void
    {
        app()->bind(
            'attachment.manager',
            config('attachments.class_mapping.attachment_manager', AttachmentManager::class)
        );
    }
}
