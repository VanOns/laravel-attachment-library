<?php

namespace VanOns\LaravelAttachmentLibrary\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelAttachmentLibrary\Glide\Resizer
 */
class Resizer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'attachment.resizer';
    }
}
