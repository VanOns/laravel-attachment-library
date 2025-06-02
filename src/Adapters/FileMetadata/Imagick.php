<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Exceptions\ClassDoesNotExistException;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 *  An adapter class for the PHP-Imagick extension.
 */
class Imagick extends MetadataAdapter
{
    /**
     * @throws ClassDoesNotExistException if Imagick is not installed.
     * @throws \ImagickException
     */
    protected function retrieve(Attachment $file): FileMetadata|bool
    {
        if (! class_exists(\Imagick::class) || ! extension_loaded('imagick')) {
            throw new ClassDoesNotExistException(\Imagick::class);
        }

        if (!$file->isImage()) {
            return false;
        }

        try {
            $image = new \Imagick($file->absolute_path);
            $imageInfo = $image->identifyImage();
        } catch (\ImagickException $e) {
            return false;
        }

        return new FileMetadata(
            width: $imageInfo['geometry']['width'],
            height: $imageInfo['geometry']['height'],
            resolutionX: (int) $imageInfo['resolution']['x'],
            resolutionY: (int) $imageInfo['resolution']['y'],
            bits: $image->getImageDepth(),
            totalPages: $image->getNumberImages()
        );
    }
}
