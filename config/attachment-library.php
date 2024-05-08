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
        'directory' => \VanOns\LaravelAttachmentLibrary\Directory::class,
        'attachment_manager' => \VanOns\LaravelAttachmentLibrary\AttachmentManager::class,
    ],

    'attachment_type_mapping' => [
        \VanOns\LaravelAttachmentLibrary\Enums\AttachmentTypes::PREVIEWABLE => ['png', 'jpg', 'svg'],
    ],
];
