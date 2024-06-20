<?php

namespace VanOns\LaravelAttachmentLibrary;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\View\Components\Image;

class LaravelAttachmentLibraryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-attachment-library')
            ->hasConfigFile(['glide'])
            ->hasMigrations(['create_attachments_table', 'create_attachables_table'])
            ->runsMigrations()
            ->hasViews('laravel-attachment-library')
            ->hasViewComponent('laravel-attachment-library', Image::class)
            ->hasRoutes('../routes/web')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile()
                    ->publishMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->setHidden(false)
                    ->askToRunMigrations();
            });
    }

    /**
     * @throws IncompatibleClassMappingException
     */
    public function bootingPackage()
    {
        $attachmentManagerClass = config('attachment-library.class_mapping.attachment_manager', AttachmentManager::class);

        if (! is_a($attachmentManagerClass, AttachmentManager::class, true)) {
            throw new IncompatibleClassMappingException($attachmentManagerClass, AttachmentManager::class);
        }

        app()->bind(
            'attachment.manager',
            config('attachment-library.class_mapping.attachment_manager', AttachmentManager::class)
        );
    }
}
