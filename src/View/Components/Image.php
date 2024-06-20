<?php

namespace VanOns\LaravelAttachmentLibrary\View\Components;

use Illuminate\View\Component;
use VanOns\LaravelAttachmentLibrary\Facades\SizeParser;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class Image extends Component
{
    public null|string|int|Attachment $src;

    public array $sizes;
    public string|float|null $aspectRatio;
    public array $breakpoints;
    public array $formats;
    public string $class;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(null|string|int|Attachment $src = null, string $sizes = 'full', string|float $aspectRatio = null, string $class = '')
    {
        $this->src = $src;
        $this->sizes = SizeParser::parse($sizes);
        $this->aspectRatio = $aspectRatio;
        $this->breakpoints = config('glide.breakpoints');
        $this->formats = config('glide.formats');
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('laravel-attachment-library::components.image');
    }
}
