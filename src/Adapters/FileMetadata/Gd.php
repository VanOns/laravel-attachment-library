<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;

/**
 *  An adapter class for the PHP-GD extension.
 */
class Gd extends MetadataAdapter
{
    protected function retrieve(string $path): FileMetadata|bool
    {
        try {
            $imageInfo = getimagesize($path);
        } catch (\ErrorException $e) {
            return false;
        }

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
