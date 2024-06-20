<?php

namespace VanOns\LaravelAttachmentLibrary\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelAttachmentLibrary\Glide\SizeParser
 */
class SizeParser extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'attachment.size.parser';
    }
}
