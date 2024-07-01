<?php

namespace VanOns\LaravelAttachmentLibrary\View\Components;

use Illuminate\View\Component;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class Image extends Component
{
    public null|string|int|Attachment $src;

    public string $size;

    public string|float|null $aspectRatio;

    public array $breakpoints;

    public array $formats;

    public string $class;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(null|string|int|Attachment $src = null, string $size = 'full', string|float|null $aspectRatio = null, string $class = '')
    {
        $this->src = $src;
        $this->size = $size;
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
