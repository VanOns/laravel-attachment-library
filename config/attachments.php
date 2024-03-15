<?php

return [
    /**
     * Default disk to use for storing attachments.
     *
     * @see filesystems.php
     */
    'disk' => 'public',

    /**
     * Database representation of the physical file.
     */
    'model' => \VanOns\LaravelAttachmentLibrary\Models\Attachment::class
];
