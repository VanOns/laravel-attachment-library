<?php

namespace VanOns\LaravelAttachmentLibrary;

use VanOns\LaravelAttachmentLibrary\AttachmentManager;
use Illuminate\Support\ServiceProvider;

class LaravelAttachmentLibraryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/attachments.php' => config_path('attachments.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->bind('attachment.manager', AttachmentManager::class);
    }
}
