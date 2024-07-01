<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class Resizer
{
    public ?string $path = null;

    public ?int $width = null;

    public ?int $height = null;

    public ?string $format = 'jpg';

    public ?string $size = 'full';

    public ?float $aspectRatio = null;

    public function __construct(public array $sizes)
    {
    }

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

    public function calculateWidth(): ?float
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

    public function calculateHeight(): ?float
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
        $file = AttachmentManager::file($this->path);

        if ($file && Str::startsWith($file->mime_type, 'image/')) {
            [$width, $height] = getimagesize($file->absolute_path);

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
            /* @var Attachment $attachment */
            $attachment = Attachment::find($src);

            return $attachment->full_path;
        }

        if ($src instanceof Attachment) {
            return $src->full_path;
        }

        return $this->getPathFromSrcPath($src);
    }

    /**
     * @throws \Exception
     */
    protected function getPathFromSrcPath(string $src): ?string
    {
        $attachment = AttachmentManager::file($src);

        if ($attachment === null) {
            throw new \Exception('Could not generate a path from the given src: '.$src);
        }

        return $attachment->full_path;
    }

    public function cacheKey(): string
    {
        return sha1($this->path.$this->width.$this->height.$this->format.$this->size.$this->aspectRatio);
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
