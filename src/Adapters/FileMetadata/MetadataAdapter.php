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

        $cached = Cache::remember(
            implode('-', [$this->cacheKey, hash('sha256', $path)]),
            now()->addDay(),
            function () use ($file) {
                $result = $this->retrieve($file);

                // Cache::remember() runs the value through PHP's native serialize()/
                // unserialize(). Consuming apps may restrict which classes are allowed
                // to be unserialized from cache (Laravel's cache.serializable_classes,
                // false by default since Laravel 11.x). Caching a plain array instead of
                // the DTO keeps this working under that restriction without requiring
                // every consumer to allowlist FileMetadata.
                return $result instanceof FileMetadata ? get_object_vars($result) : $result;
            }
        );

        return is_array($cached) ? new FileMetadata(...$cached) : $cached;
    }

    abstract protected function retrieve(Attachment $file): FileMetadata|bool;
}
