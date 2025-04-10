<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;

class GlideManager
{
    public function server(): Server
    {
        return ServerFactory::create([
            'driver' => config('glide.driver'),
            'source' => config('glide.source'),
            'cache' => $this->cacheDisk()->getDriver(),
            'defaults' => config('glide.defaults'),
            'presets' => config('glide.presets'),
            'max_image_size' => config('glide.max_image_size'),
            'response' => new SymfonyResponseFactory(),
            'cache_path_callable' => function ($path, $params) {
                return app(OptionsParser::class)->toString($params) . '/' . $path;
            },
        ]);
    }

    public function cacheDisk(): Filesystem
    {
        return is_string(config('glide.cache_disk'))
            ? Storage::disk(config('glide.cache_disk'))
            : Storage::build(config('glide.cache_disk'));
    }
}
