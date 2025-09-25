<?php

namespace VanOns\LaravelAttachmentLibrary\View\Components;

use Illuminate\View\Component;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Filename;
use VanOns\LaravelAttachmentLibrary\Facades\Glide;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Blade component for wrapping Glide images responsively.
 *
 * It allows for various configurations such as source, size, aspect ratio, CSS classes, and responsive breakpoints.
 */
class Image extends Component
{
    public array $breakpoints;

    public array $formats;

    public ?Attachment $attachment;

    public bool $supportedByGlide;

    public function __construct(
        public string|int|Attachment|null $src = null,
        public string $size = 'full',
        public string|float|null $aspectRatio = null,
        public string $class = ''
    ) {
        $this->breakpoints = config('glide.breakpoints');
        $this->formats = config('glide.formats');
        $this->attachment = $this->retrieveAttachment();
        $this->supportedByGlide = $this->getGlideSupport();
    }

    public function render()
    {
        return view('laravel-attachment-library::components.image');
    }

    protected function retrieveAttachment(): ?Attachment
    {
        if ($this->src instanceof Attachment) {
            return $this->src;
        }

        if (is_numeric($this->src)) {
            return Attachment::find($this->src);
        }

        if (is_string($this->src)) {
            return Attachment::whereFilename(new Filename($this->src))->first();
        }

        return null;
    }

    protected function getGlideSupport(): bool
    {
        if (!$this->attachment) {
            return false;
        }

        return Glide::imageIsSupported($this->attachment->full_path);
    }
}
