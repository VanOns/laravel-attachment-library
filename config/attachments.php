<?php

return [
    /**
     * Default disk to use for storing attachments.
     *
     * @see filesystems.php
     */
    'disk' => env('ATTACHMENTS_DISK') ?? 'public',

    /**
     * Database representation of the physical file.
     */
    'model' => \VanOns\LaravelAttachmentLibrary\Models\Attachment::class,
];
