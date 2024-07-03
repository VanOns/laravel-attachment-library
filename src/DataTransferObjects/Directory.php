<?php

namespace VanOns\LaravelAttachmentLibrary\DataTransferObjects;

/**
 * Data Transfer Object for directories.
 */
readonly class Directory
{
    public ?string $path;

    public string $name;

    public string $fullPath;

    public function __construct(string $directoryPath)
    {
        $this->fullPath = $directoryPath;

        $path = explode('/', $directoryPath);

        $this->name = array_pop($path);

        $path = implode('/', $path);
        $this->path = $path !== '' ? $path : null;
    }
}
