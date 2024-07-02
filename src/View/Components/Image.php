<?php

namespace VanOns\LaravelAttachmentLibrary\View\Components;

use Illuminate\View\Component;
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

    public function __construct(
        public null|string|int|Attachment $src = null,
        public string $size = 'full',
        public string|float|null $aspectRatio = null,
        public string $class = ''
    ) {
        $this->breakpoints = config('glide.breakpoints');
        $this->formats = config('glide.formats');
    }

    public function render()
    {
        return view('laravel-attachment-library::components.image');
    }
}
