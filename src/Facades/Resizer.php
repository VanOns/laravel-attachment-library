<?php

namespace VanOns\LaravelAttachmentLibrary\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelAttachmentLibrary\Glide\Resizer
 */
class Resizer extends Facade
{
    /**
     * A resizer keeps its options between calls, so every call needs a fresh instance.
     */
    protected static $cached = false;

    protected static function getFacadeAccessor(): string
    {
        return 'attachment.resizer';
    }
}
