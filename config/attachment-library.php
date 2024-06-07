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
        'directory' => \VanOns\LaravelAttachmentLibrary\DataTransferObjects\Directory::class,
        'attachment_manager' => \VanOns\LaravelAttachmentLibrary\AttachmentManager::class,
    ],

    'attachment_mime_type_mapping' => [
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_IMAGE => [
            'image/jpeg', 'image/apng', 'image/apng', 'image/svg', 'image/vnd.microsoft.icon',
            'image/avif', 'image/svg+xml', 'image/webp',
        ],
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_VIDEO => [
            'video/x-msvideo', 'video/mp4', 'video/mpeg', 'video/ogg', 'video/mp2t',
            'video/webm', 'video/3gpp', 'video/3gpp2',
        ],
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_AUDIO => [
            'audio/aac', 'audio/midi', 'audio/x-midi', 'audio/mpeg', 'audio/ogg',
            'audio/opus', 'audio/wav', 'audio/webm', '', 'audio/3gpp', 'audio/3gpp2',
        ],
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_DOCUMENT => [
            'application/pdf', 'application/xml', 'text/xml', 'text/plain',
        ],
    ],
];
