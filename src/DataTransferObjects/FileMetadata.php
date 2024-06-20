<?php

namespace VanOns\LaravelAttachmentLibrary\DataTransferObjects;

/**
 *  A DTO class for file metadata.
 */
readonly class FileMetadata
{
    public function __construct(
        /**
         * Image related metadata.
         */
        public ?int $width,
        public ?int $height,
        public ?int $resolutionX = null,
        public ?int $resolutionY = null,
        public ?int $bits = null,
        public ?int $channels = null,

        /**
         * PDF related metadata.
         */
        public ?int $totalPages = null,

        /**
         * Video related metadata.
         */
        public ?int $videoDuration = null,
    ) {
    }
}
