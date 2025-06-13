<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 *  An adapter class for the PHP-GD extension.
 */
class Gd extends MetadataAdapter
{
    protected function retrieve(Attachment $file): FileMetadata|bool
    {
        $imageInfo = AttachmentManager::getImageSizes($file);
        if (! $imageInfo) {
            return false;
        }

        return new FileMetadata(
            width: $imageInfo[0],
            height: $imageInfo[1],
            bits: $imageInfo['bits'] ?? null,
            channels: $imageInfo['channels'] ?? null
        );
    }
}
