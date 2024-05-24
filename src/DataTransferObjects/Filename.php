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

    public function __construct(UploadedFile $file)
    {
        $filename = $file->getClientOriginalName();
        $this->name = pathinfo($filename, PATHINFO_FILENAME);

        // Guess extension based on MIME-Type or grab extension from filename.
        $extension = $file->guessExtension() ?? pathinfo($filename, PATHINFO_EXTENSION);

        // Set extension to null if extension isn't known.
        $this->extension = $extension === '' ? null : $extension;
    }

    public function __toString(): string
    {
        return implode('.', array_filter([$this->name, $this->extension]));
    }
}
