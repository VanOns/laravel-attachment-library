<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Exceptions\ClassDoesNotExistException;

/**
 *  An adapter class for the PHP-Imagick extension.
 */
class Imagick extends MetadataAdapter
{
    /**
     * @throws ClassDoesNotExistException if Imagick is not installed.
     * @throws \ImagickException
     */
    protected function retrieve(string $path): FileMetadata|bool
    {
        if (! class_exists(\Imagick::class) || ! extension_loaded('imagick')) {
            throw new ClassDoesNotExistException(\Imagick::class);
        }

        try {
            $image = new \Imagick($path);
        } catch (\ImagickException $e) {
            return false;
        }

        $imageInfo = $image->identifyImage();

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
