<?php

namespace VanOns\LaravelAttachmentLibrary\DataTransferObjects;

use Illuminate\Http\UploadedFile;

/**
 * Data Transfer Object for filenames.
 *
 * Used to turn untrusted user input into trusted filename.
 */
readonly class Filename
{
    public string $name;

    public ?string $extension;

    public function __construct(UploadedFile|string $file)
    {
        if ($file instanceof UploadedFile) {
            $file = $file->getClientOriginalName();
        }

        $this->name = pathinfo($file, PATHINFO_FILENAME);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $this->extension = $extension === '' ? null : $extension;
    }

    public function __toString(): string
    {
        return implode('.', array_filter([$this->name, $this->extension]));
    }
}
