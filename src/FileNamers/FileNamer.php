<?php

namespace VanOns\LaravelAttachmentLibrary\FileNamers;

abstract class FileNamer
{
    abstract public function execute(string $value): string;
}
