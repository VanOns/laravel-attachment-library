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
            'driver' => $this->driver(),
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

    public function driver(): string
    {
        return config('glide.driver', 'gd');
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
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / (1024 ** $factor)) . ' ' . $size[$factor];
    }

    public function imageIsSupported(string $path, array $params = []): bool
    {
        try {
            $this->server()->makeImage($path, $params);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Retrieve supported image formats for the current driver.
     *
     * @param bool $onlyCommon Limit to results to the most common formats.
     */
    public function getSupportedImageFormats(bool $onlyCommon = true): array
    {
        $commonFormats = [
            'AVIF',
            'BMP',
            'GIF',
            'HEIC',
            'HEIF',
            'ICO',
            'JPEG',
            'JPG',
            'PNG',
            'SVG',
            'TIFF',
            'WEBP',
        ];

        $driver = $this->driver();

        if ($driver === 'gd' && function_exists('gd_info')) {
            $formats = gd_info();

            $supported = collect();
            foreach ($formats as $key => $value) {
                if ($value === false) {
                    continue;
                }

                if ($onlyCommon) {
                    foreach ($commonFormats as $format) {
                        if (str_contains($key, $format)) {
                            $supported->push($format);
                            break;
                        }
                    }
                } else {
                    $format = strtoupper(str_replace([' ', 'Support'], '', $key));
                    $supported->push($format);
                }
            }

            return $supported
                ->unique()
                ->values()
                ->sort()
                ->all();
        }

        if ($driver === 'imagick' && class_exists(\Imagick::class)) {
            $formats = \Imagick::queryFormats();

            return collect()
                ->when($onlyCommon, fn ($collection) => $collection->merge($commonFormats)->filter(fn ($format) => in_array($format, $formats)))
                ->when(!$onlyCommon, fn ($collection) => $collection->merge($formats))
                ->unique()
                ->values()
                ->sort()
                ->all();
        }

        return [];
    }
}
