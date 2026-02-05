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
    public bool $isVisible;

    public function __construct(string $directoryPath, ?\Closure $isInDirectory = null)
    {
        $this->fullPath = $directoryPath;

        $this->isVisible = $isInDirectory
            ? $isInDirectory($directoryPath)
            : true;

        $path = explode('/', $directoryPath);

        $this->name = array_pop($path);

        $path = implode('/', $path);
        $this->path = $path !== '' ? $path : null;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }
}
