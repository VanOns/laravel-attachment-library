<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use Illuminate\Support\Facades\Cache;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

abstract class MetadataAdapter
{
    protected string $cacheKey = 'metadata-adapter';

    public function getMetadata(Attachment $file): FileMetadata|bool
    {
        $path = $file->absolute_path;

        return Cache::remember(
            implode('-', [$this->cacheKey, hash('sha256', $path)]),
            now()->addDay(),
            fn () => $this->retrieve($file)
        );
    }

    abstract protected function retrieve(Attachment $file): FileMetadata|bool;
}
