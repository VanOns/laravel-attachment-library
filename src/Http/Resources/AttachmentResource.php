<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * @mixin Attachment
 */
class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'name' => $this->name,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'full_path' => $this->full_path,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'focal_point' => $this->focal_point,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'aspect_ratio' => $this->aspect_ratio,
            'poster' => new AttachmentResource($this->whenLoaded('poster')),
            'captions' => $this->whenLoaded('captions', fn () => $this->captions->map(fn (Attachment $caption) => [
                'url' => $caption->url,
                'language' => $caption->pivot->language,
                'label' => $caption->pivot->label,
                'is_default' => $caption->pivot->is_default,
            ])),
        ];
    }
}
