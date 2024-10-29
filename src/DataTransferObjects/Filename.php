<?php

namespace VanOns\LaravelAttachmentLibrary\DataTransferObjects;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\Exceptions\ClassDoesNotExistException;
use VanOns\LaravelAttachmentLibrary\Exceptions\IncompatibleClassMappingException;
use VanOns\LaravelAttachmentLibrary\FileNamers\FileNamer;

/**
 * Data Transfer Object for filenames.
 *
 * Used to turn untrusted user input into trusted filename.
 */
readonly class Filename
{
    public string $name;

    public ?string $extension;

    public ?string $path;

    public function __construct(UploadedFile|string $file)
    {
        if ($file instanceof UploadedFile) {
            $file = $file->getClientOriginalName();
        }

        $pathInfo = pathinfo($file);
        $this->name = $this->formatFilename($pathInfo['filename']);
        $this->extension = $pathInfo['extension'] === '' ? null : $pathInfo['extension'];
        $this->path = $pathInfo['dirname'] === '.' ? null : $pathInfo['dirname'];
    }

    /**
     * Return filename including extension.
     */
    public function __toString(): string
    {
        return implode('.', array_filter([$this->name, $this->extension]));
    }

    /**
     * Formats given filename according to configured FileNamers.
     */
    private function formatFilename(string $name): string
    {
        $fileNamers = Config::get('attachment-library.file_namers', []);

        foreach (array_keys($fileNamers) as $fileNamer) {
            $this->validateFileNamer($fileNamer);

            $name = (new $fileNamer())->execute($name);
        }

        return $name;
    }

    /**
     * Performs validation on a FileNamer.
     *
     * @throws ClassDoesNotExistException if $fileNamer isn't a class
     * @throws IncompatibleClassMappingException if $fileNamer does not extend FileNamer
     */
    private function validateFileNamer(string $fileNamer): void
    {
        if (! class_exists($fileNamer)) {
            throw new ClassDoesNotExistException($fileNamer);
        }
        if (! is_a($fileNamer, FileNamer::class, true)) {
            throw new IncompatibleClassMappingException($fileNamer, FileNamer::class);
        }
    }
}
