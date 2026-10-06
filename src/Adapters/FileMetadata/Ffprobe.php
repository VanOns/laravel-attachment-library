<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Facades\Ffmpeg;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * An adapter class for the ffprobe binary.
 */
class Ffprobe extends MetadataAdapter
{
    protected function retrieve(Attachment $file): FileMetadata|bool
    {
        if (! $file->isVideo() || ! Ffmpeg::isAvailable()) {
            return false;
        }

        $probe = Ffmpeg::probe($file);

        if (! $probe) {
            return false;
        }

        return new FileMetadata(
            width: $probe['width'],
            height: $probe['height'],
            videoDuration: $probe['duration'],
        );
    }
}
