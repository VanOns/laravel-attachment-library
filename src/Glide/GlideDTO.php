<?php

namespace VanOns\LaravelAttachmentLibrary\Glide;

use Illuminate\Support\Facades\URL;
use InvalidArgumentException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class GlideDTO
{
    /**
     * @see https://glide.thephpleague.com/2.0/api/quick-reference/
     */
    public ?string $or = null;

    public ?string $flip = null;

    public ?string $crop = null;

    public ?string $w = null;

    public ?string $h = null;

    public ?string $fit = null;

    public ?string $dpr = null;

    public ?string $bri = null;

    public ?string $con = null;

    public ?string $gam = null;

    public ?string $sharp = null;

    public ?string $blur = null;

    public ?string $pixel = null;

    public ?string $filt = null;

    public ?string $mark = null;

    public ?string $markw = null;

    public ?string $markh = null;

    public ?string $markx = null;

    public ?string $marky = null;

    public ?string $markfit = null;

    public ?string $markpad = null;

    public ?string $markpos = null;

    public ?string $markalpha = null;

    public ?string $bg = null;

    public ?string $border = null;

    public ?string $q = null;

    public ?string $fm = null;

    /**
     * @throws InvalidArgumentException if key doesn't exist.
     */
    public function set(string $key, string $value): static
    {
        if (! property_exists($this, $key)) {
            throw new \InvalidArgumentException("Property {$key} not found in ".GlideDTO::class);
        }
        $this->{$key} = $value;

        return $this;
    }

    /**
     * @throws InvalidArgumentException if key doesn't exist.
     */
    public function get(string $key): string
    {
        if (! property_exists($this, $key)) {
            throw new \InvalidArgumentException("Property {$key} not found in ".GlideDTO::class);
        }

        return $this->{$key};
    }

    public function url(string|Attachment $path): string
    {
        if ($path instanceof Attachment) {
            $path = $path->full_path;
        }

        return URL::signedRoute('glide', ['path' => $path, ...$this->toArray()]);
    }

    protected function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
