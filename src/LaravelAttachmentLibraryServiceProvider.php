<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VanOns\LaravelAttachmentLibrary\Console\Commands\ProcessVideos;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\Observers\AttachmentObserver;
use VanOns\LaravelAttachmentLibrary\Video\Ffmpeg;
use VanOns\LaravelAttachmentLibrary\View\Components\Image;
use VanOns\LaravelAttachmentLibrary\View\Components\Video;

class LaravelAttachmentLibraryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-attachment-library')
            ->hasConfigFile(['attachment-library', 'glide'])
            ->hasMigrations(['create_attachments_table', 'create_attachables_table', 'add_collection_to_attachables_table', 'add_focal_point_to_attachments_table', 'add_order_to_attachables_table', 'add_video_fields_to_attachments_table', 'create_attachment_captions_table'])
            ->runsMigrations()
            ->hasViews('laravel-attachment-library')
            ->hasViewComponents('laravel-attachment-library', Image::class, Video::class)
            ->hasCommand(ProcessVideos::class)
            ->hasRoutes('../routes/web')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile()
                    ->publishMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->setHidden(false)
                    ->askToRunMigrations();
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('attachment.ffmpeg', Ffmpeg::class);
    }

    /**
     * @throws IncompatibleClassMappingException
     */
    public function bootingPackage(): void
    {
        $attachmentManagerClass = config('attachment-library.class_mapping.attachment_manager', AttachmentManager::class);

        if (! is_a($attachmentManagerClass, AttachmentManager::class, true)) {
            throw new IncompatibleClassMappingException($attachmentManagerClass, AttachmentManager::class);
        }

        app()->bind('attachment.manager', $attachmentManagerClass);

        Config::get('attachment-library.class_mapping.attachment', Attachment::class)::observe(AttachmentObserver::class);
    }
}
