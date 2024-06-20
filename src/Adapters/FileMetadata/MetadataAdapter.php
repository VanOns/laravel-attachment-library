<?php

namespace VanOns\LaravelAttachmentLibrary\Adapters\FileMetadata;

use Illuminate\Support\Facades\Cache;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\FileMetadata;

abstract class MetadataAdapter
{
    protected string $cacheKey = 'imageadapter';

    public function getMetadata(string $path): FileMetadata|bool
    {
        $cacheKey = "{$this->cacheKey}-{$path}";
        $cachedItem = Cache::get($cacheKey);

        if ($cachedItem !== null) {
            return $cachedItem;
        }

        $item = $this->retrieve($path);

        Cache::set($cacheKey, $item);

        return $item;
    }

    abstract protected function retrieve(string $path): FileMetadata|bool;
}
