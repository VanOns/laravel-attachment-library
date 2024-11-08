<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Database\Eloquent\Builder;
use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Filename;

/**
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 * @extends Builder<TModelClass>
 */
class AttachmentQueryBuilder extends Builder
{
    /**
     * Filter files by disk.
     */
    public function whereDisk(string $disk): static
    {
        return $this->where('disk', $disk);
    }

    /**
     * Filter files by exact path.
     */
    public function wherePath(?string $path): static
    {
        return $this->where('path', $path);
    }

    /**
     * Filter all files in path including in subdirectories.
     */
    public function whereInPath(string $path): static
    {
        return $this->where('path', '=', $path)
            ->orWhere('path', 'LIKE', "{$path}/%");
    }

    /**
     * Filter files by filename DTO.
     */
    public function whereFilename(Filename $filename): static
    {
        return $this->where('path', '=', $filename->path)
            ->where('name', '=', $filename->name)
            ->where('extension', '=', $filename->extension);
    }
}
