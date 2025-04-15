<?php

return [

    /**
     * Default disk to use for storing attachments.
     *
     * @see filesystems.php
     */
    'disk' => env('ATTACHMENTS_DISK', 'public'),

    /**
     * Map file metadata retrievers to array of MIME-types.
     *
     * Use '*' for wildcard.
     */
    'metadata_retrievers' => [
        \VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata\Gd::class => ['image/*'],
    ],

    /**
     * Regular expression for defining allowed characters for file and directory names.
     */
    'allowed_characters' => '/[^\\pL\\pN_\.\- ()\x{AD}]+/u',

    /**
     * Class mapping for objects used in package.
     */
    'class_mapping' => [
        'attachment' => \VanOns\LaravelAttachmentLibrary\Models\Attachment::class,
        'attachment_manager' => \VanOns\LaravelAttachmentLibrary\AttachmentManager::class,
        'directory' => \VanOns\LaravelAttachmentLibrary\DataTransferObjects\Directory::class,
    ],

    /**
     * Map AttachmentType to array of MIME-types.
     */
    'attachment_mime_type_mapping' => [
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentType::PREVIEWABLE_IMAGE => [
            'image/apng',
            'image/avif',
            'image/jpeg',
            'image/png',
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

    /**
     * Classes including configuration that manipulate file names.
     */
    'file_namers' => [
        \VanOns\LaravelAttachmentLibrary\FileNamers\ReplaceControlCharacters::class => [
            'search' => [
                "/\u{AD}/u",
                "/[\x{00A0}\x{1680}\x{180E}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}\x{FEFF}]/u", // Whitespace characters
                "/\p{C}/u", // To prevent corrupted path exception in WhiteSpaceNormalizer
            ],
            'replace' => [
                '-',
                ' ',
                '',
            ],
        ],
    ],

];
