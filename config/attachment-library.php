<?php

return [
    /**
     * Default disk to use for storing attachments.
     *
     * @see filesystems.php
     */
    'disk' => env('ATTACHMENTS_DISK', 'public'),

    /**
     * Regular expression for defining allowed characters for file and directory names.
     */
    'allowed_characters' => '/[^\\pL\\pN_\.\- ]+/u',

    /**
     * Class mapping for objects used in package.
     */
    'class_mapping' => [
        'attachment' => \VanOns\LaravelAttachmentLibrary\Models\Attachment::class,
        'attachment_manager' => \VanOns\LaravelAttachmentLibrary\AttachmentManager::class,
        'directory' => \VanOns\LaravelAttachmentLibrary\DataTransferObjects\Directory::class,
    ],

    /**
     * Map AttachmentType to MIME-types.
     */
    'attachment_mime_type_mapping' => [
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_IMAGE => [
            'image/apng',
            'image/apng',
            'image/avif',
            'image/jpeg',
            'image/svg',
            'image/svg+xml',
            'image/vnd.microsoft.icon',
            'image/webp',
        ],
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_VIDEO => [
            'video/3gpp',
            'video/3gpp2',
            'video/mp2t',
            'video/mp4',
            'video/mpeg',
            'video/ogg',
            'video/webm',
            'video/x-msvideo',
        ],
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_AUDIO => [
            'audio/3gpp',
            'audio/3gpp2',
            'audio/aac',
            'audio/midi',
            'audio/mpeg',
            'audio/ogg',
            'audio/opus',
            'audio/wav',
            'audio/webm',
            'audio/x-midi',
        ],
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_DOCUMENT => [
            'application/pdf',
            'application/xml',
            'text/plain',
            'text/xml',
        ],
    ],
];
