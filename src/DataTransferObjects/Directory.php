<?php

namespace VanOns\LaravelAttachmentLibrary\DataTransferObjects;

use VanOns\LaravelAttachmentLibrary\Facades\AttachmentManager;

/**
 * Data Transfer Object for directories.
 */
readonly class Directory
{
    public ?string $path;

    public string $name;

    public string $fullPath;

    public bool $isVisible;

    public function __construct(string $directoryPath, bool $checkVisibility = false)
    {
        $this->fullPath = $directoryPath;

        $this->isVisible = $checkVisibility
            ? AttachmentManager::isInDirectory($directoryPath)
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
