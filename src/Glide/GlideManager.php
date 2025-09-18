<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Exception;
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

    /**
     * @return array{
     *     files: int,
     *     size: int,
     *     readable_size: string
     * }
     */
    public function cacheStats(): array
    {
        return [
            'files' => $this->cacheFiles(),
            'size' => $this->cacheSize(),
            'readable_size' => $this->cacheSizeHumanReadable(),
        ];
    }

    public function cacheFiles(): int
    {
        return count($this->cacheDisk()->allFiles());
    }

    public function cacheSize(): int
    {
        return collect($this->cacheDisk()->allFiles())->sum(
            fn ($file) => $this->cacheDisk()->size($file)
        );
    }

    public function cacheSizeHumanReadable(): string
    {

        return $this->humanReadableSize($this->cacheSize());
    }

    public function humanReadableSize(int $bytes, $decimals = 2): string
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen(strval($bytes)) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
    }

    public function imageIsSupported(string $path, array $params = []): bool
    {
        // When running inside unit tests, the files are mocked, so they don't actually exist on the filesystem.
        if (app()->runningUnitTests()) {
            return true;
        }

        try {
            $this->server()->makeImage($path, $params);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}
