<?php

namespace VanOns\LaravelAttachmentLibrary;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;

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

    /**
     * @throws IncompatibleClassMappingException
     */
    public function packageBooted(): void
    {
        $attachmentManagerClass = config('attachments.class_mapping.attachment_manager', AttachmentManager::class);

        if (! is_a($attachmentManagerClass, AttachmentManager::class, true)) {
            throw new IncompatibleClassMappingException($attachmentManagerClass, AttachmentManager::class);
        }

        app()->bind(
            'attachment.manager',
            config('attachments.class_mapping.attachment_manager', AttachmentManager::class)
        );
    }
}
