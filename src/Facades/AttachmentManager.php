<?php

namespace VanOns\LaravelAttachmentLibrary\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelAttachmentLibrary\AttachmentManager
 */
class AttachmentManager extends Facade
{
    /**
     * Return the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'attachment.manager';
    }
}
