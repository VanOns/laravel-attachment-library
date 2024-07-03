<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Generates signed URLs to GlideController based on images.
 */
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

    /**
     * Manually set a path to the image.
     */
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

    /**
     * Calculate the width of the image based on size/aspect ratio or height.
     */
    public function calculateWidth(): ?float
    {
        // Return width based on size ratio.
        if ($this->width) {
            return round($this->width * $this->getSizeRatio());
        }

        // Return width based on height and aspect ratio.
        if ($this->height && $this->aspectRatio) {
            return round($this->calculateHeight() * $this->aspectRatio);
        }

        // Return width based on image dimensions.
        if ($this->height) {
            [$width, $height] = $this->getImageSize();

            return ! empty($height)
                ? round($this->calculateHeight() / $height * $width)
                : 0;
        }

        return null;
    }

    /**
     * Calculate the height of the image based on size/aspect ratio or width.
     */
    public function calculateHeight(): ?float
    {
        // Return width based on size ratio.
        if ($this->height) {
            return round($this->height * $this->getSizeRatio());
        }

        // Return width based on height and aspect ratio.
        if ($this->width && $this->aspectRatio) {
            return round(($this->calculateWidth() / $this->aspectRatio));
        }

        // Return height based on image dimensions.
        if ($this->width) {
            [$width, $height] = $this->getImageSize();

            return ! empty($width)
                ? round($this->calculateWidth() / $width * $height)
                : 0;
        }

        return null;
    }

    /**
     * Set the desired size ratio.
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the current size ratio.
     */
    public function getSizeRatio(): float
    {
        return $this->sizes[$this->size] ?? 1;
    }

    /**
     * Get the dimensions of the source image.
     */
    public function getImageSize(): ?array
    {
        $file = AttachmentManager::file($this->path);

        if ($file && Str::startsWith($file->mime_type, 'image/')) {
            [$width, $height] = getimagesize($file->absolute_path);

            return [$width, $height];
        }

        return null;
    }

    /**
     * Set the aspect ratio for the image.
     *
     * @param  string|float|null  $aspectRatio  Aspect ratio as a float or a string in the format 'width/height'.
     */
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

    /**
     * Set the desired format (e.g., 'jpg', 'webp') for the image.
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the path to the image file based on the source.
     *
     * @throws \Exception If the path cannot be determined.
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

        return $src;
    }

    public function cacheKey(): string
    {
        return sha1($this->path.$this->width.$this->height.$this->format.$this->size.$this->aspectRatio);
    }

    /**
     * Resize the image and return an array with the signed URL, width, and height of the image.
     */
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
