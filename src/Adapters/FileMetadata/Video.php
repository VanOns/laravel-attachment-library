<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Returns the dimensions and duration stored on a video when it was processed.
 */
class Video extends MetadataAdapter
{
    /**
     * Not cached, because the stored values are cheap to read and change when the video is replaced.
     */
    public function getMetadata(Attachment $file): FileMetadata|bool
    {
        return $this->retrieve($file);
    }

    protected function retrieve(Attachment $file): FileMetadata|bool
    {
        if (! $file->isVideo() || ! $file->width) {
            return false;
        }

        return new FileMetadata(
            width: $file->width,
            height: $file->height,
            videoDuration: $file->duration,
        );
    }
}
