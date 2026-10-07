<?php

namespace VanOns\LaravelAttachmentLibrary\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VanOns\LaravelAttachmentLibrary\Video\Ffmpeg
 */
class Ffmpeg extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'attachment.ffmpeg';
    }
}
