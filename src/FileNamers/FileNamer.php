<?php

namespace VanOns\LaravelAttachmentLibrary\FileNamers;

use Illuminate\Support\Facades\Config;

abstract class FileNamer
{
    abstract public function execute(string $value): string;

    protected function getConfig(?string $key, mixed $default = null): mixed
    {
        return Config::get(implode('.', array_filter(['attachment-library.file_namers', static::class, $key])), $default);
    }
}
