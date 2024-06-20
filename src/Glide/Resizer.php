<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use League\Glide\Urls\UrlBuilder;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class Resizer
{
    public ?string $path = null;
    public ?int $width = null;
    public ?int $height = null;
    public ?string $format = 'jpg';
    public ?string $size = 'full';
    public ?float $aspectRatio = null;

    public function __construct(public array $sizes) {}

    public function src(string|int|Attachment $src): static
    {
        $this->path = $this->getPath($src);
        return $this;
    }

    public function path(?string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function width(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function calculateWidth(): ?int
    {
        if ($this->width) {
            return round($this->width * $this->getSizeRatio());
        }

        if ($this->height && $this->aspectRatio) {
            return round($this->calculateHeight() * $this->aspectRatio);
        }

        if ($this->height) {
            [$width, $height] = $this->getImageSize();

            // early return, as we can NOT divide by zero
            if (empty($height)) {
                return 0;
            }

            return round($this->calculateHeight() / $height * $width);
        }

        return null;
    }

    public function calculateHeight(): ?int
    {
        if ($this->height) {
            return round($this->height * $this->getSizeRatio());
        }

        if ($this->width && $this->aspectRatio) {
            return round(($this->calculateWidth() / $this->aspectRatio));
        }

        if ($this->width) {
            [$width, $height] = $this->getImageSize();

            // early return, as we can NOT divide by zero
            if (empty($width)) {
                return 0;
            }

            return round($this->calculateWidth() / $width * $height);
        }

        return null;
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getSizeRatio(): float
    {
        return $this->sizes[$this->size] ?? 1;
    }

    public function getImageSize(): ?array
    {
        $file = public_path($this->path);

        if (is_file($file) && Str::startsWith(mime_content_type($file), 'image/')) {
            [$width, $height,] = getimagesize($file);
            return [$width, $height];
        }

        return null;
    }

    public function aspectRatio(string|float|null $aspectRatio): static
    {
        if (is_string($aspectRatio)) {
            $parts = explode('/', $aspectRatio);
            $this->aspectRatio = intval($parts[0]) / intval($parts[1]);
        } else {
            $this->aspectRatio = $aspectRatio;
        }

        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function getPath(string|int|Attachment $src): ?string
    {
        if (is_numeric($src)) {
            return Attachment::find($src)->absolute_path;
        }

        if ($src instanceof Attachment) {
            return $src->full_path;
        }

        if (Str::isUrl($src)) {
            return $this->getPathFromSrcUrl($src);
        }

        return $this->getPathFromSrcPath($src);
    }

    /**
     * @throws \Exception
     */
    protected function getPathFromSrcPath(string $src): ?string
    {
        if (file_exists(public_path($src))) {
            return $src;
        }

        if (Str::startsWith($src, storage_path('app/public'))) {
            return Str::replace(storage_path('app/public'), 'storage', $src);
        }

        throw new \Exception('Could not generate a path from the given src: ' . $src);
    }

    /**
     * @throws \Exception
     */
    protected function getPathFromSrcUrl(string $src): ?string
    {
        if (Str::startsWith($src, config('app.url'))) {
            return Str::replace(config('app.url'), '', $src);
        }

        return $this->getPathFromExternalSrc($src);
    }

    protected function getPathFromExternalSrc(string $src): ?string
    {
        $disk = Storage::disk('public');
        $file = 'external/' . hash('sha256', $src);

        if ($disk->exists($file)) {
            return Storage::url($file);
        }

        // This is probably a bit slow, but I'm not sure if this situation occurs often enough to warrant a better solution
        if (!$data = getimagesize($src)) {
            throw new \Exception('Could not generate a path from the given src: ' . $src);
        }

        if (!Str::startsWith($data['mime'], 'image/')) {
            throw new \Exception('Could not generate a path from the given src: ' . $src);
        }

        $disk->put($file, file_get_contents($src));
        return Storage::url($file);
    }

    public function cacheKey(): string
    {
        return sha1($this->path . $this->width . $this->height . $this->format . $this->size . $this->aspectRatio);
    }

    public function resize(): array
    {
        $width = $this->calculateWidth();
        $height = $this->calculateHeight();

        $parameters = [
            'w' => $width,
            'h' => $height,
            'fm' => $this->format,
            'fit' => 'crop',
        ];

        return [
            'width' => $width,
            'height' => $height,
            'url' => URL::signedRoute('glide', ['path' => $this->path, ...$parameters]),
        ];
    }
}
