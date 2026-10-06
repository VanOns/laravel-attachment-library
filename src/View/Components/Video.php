<?php

namespace VanOns\LaravelAttachmentLibrary\View\Components;

use Illuminate\View\Component;
use VanOns\LaravelAttachmentLibrary\Facades\Resizer;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;
use VanOns\LaravelAttachmentLibrary\View\Components\Concerns\RetrievesAttachment;

/**
 * Blade component for rendering a video with its poster, intrinsic size and caption tracks.
 */
class Video extends Component
{
    use RetrievesAttachment;

    public ?Attachment $attachment;

    public ?string $posterUrl;

    public function __construct(
        public string|int|Attachment|null $src = null,
    ) {
        $this->attachment = $this->retrieveAttachment();
        $this->posterUrl = $this->retrievePosterUrl();
    }

    public function render()
    {
        return view('laravel-attachment-library::components.video');
    }

    protected function retrievePosterUrl(): ?string
    {
        $poster = $this->attachment?->poster;

        if (! $poster) {
            return null;
        }

        $resizer = Resizer::src($poster);

        if ($this->attachment->width) {
            $resizer->width($this->attachment->width);
        }

        return $resizer->resize()['url'] ?? $poster->url;
    }
}
