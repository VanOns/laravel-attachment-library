<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Support\ServiceProvider;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use VanOns\LaravelAttachmentLibrary\Glide\Resizer;
use VanOns\LaravelAttachmentLibrary\Glide\SizeParser;

class GlideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        app()->bind(Server::class, function () {
            return ServerFactory::create([
                'driver' => config('glide.driver'),
                'source' => config('glide.source'),
                'cache' => config('glide.cache'),
                'defaults' => config('glide.defaults'),
                'presets' => config('glide.presets'),
                'max_image_size' => config('glide.max_image_size'),
                'response' => new SymfonyResponseFactory(),
                'base_url' => '/img/',
            ]);
        });

        $this->app->bind('attachment.size.parser', function () {
            return new SizeParser(config('glide.breakpoints'));
        });

        app()->bind('attachment.resizer', function () {
            return new Resizer(
                config('glide.sizes')
            );
        });
    }
}
