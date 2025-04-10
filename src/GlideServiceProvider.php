<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use VanOns\LaravelAttachmentLibrary\Console\Commands\ClearGlide;
use VanOns\LaravelAttachmentLibrary\Console\Commands\GlideStats;
use VanOns\LaravelAttachmentLibrary\Facades\Glide;
use VanOns\LaravelAttachmentLibrary\Glide\GlideManager;
use VanOns\LaravelAttachmentLibrary\Glide\OptionsParser;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;

class GlideServiceProvider extends ServiceProvider
{
    /**
     * Register Glide server and resizer.
     */
    public function register(): void
    {
        config([
            'filesystems.links' => array_merge(
                config('filesystems.links', []),
                config('glide.links', [])
            ),
        ]);

        app()->bind(Server::class, function () {
            return Glide::server();
        });

        app()->bind('attachment.resizer', function () {
            return new Resizer(config('glide.sizes'));
        });

        app()->bind('attachment.glide.manager', GlideManager::class);
    }

    public function boot(): void
    {
        $this->commands([ ClearGlide::class, GlideStats::class ]);
    }
}
