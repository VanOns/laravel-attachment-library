<?php

namespace VanOns\LaravelAttachmentLibrary\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelAttachmentLibrary\Glide\GlideManager
 */
class Glide extends Facade
{
    /**
     * Return the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'attachment.glide.manager';
    }
}
