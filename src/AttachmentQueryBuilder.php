<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Database\Eloquent\Builder;

class AttachmentQueryBuilder extends Builder
{
    /**
     * Filter files by disk.
     */
    public function whereDisk(string $disk): AttachmentQueryBuilder
    {
        return $this->where('disk', $disk);
    }

    /**
     * Filter files by exact path.
     */
    public function wherePath(?string $path): AttachmentQueryBuilder
    {
        return $this->where('path', $path);
    }

    /**
     * Filter all files in path including in subdirectories.
     */
    public function whereInPath(string $path): AttachmentQueryBuilder
    {
        $path = preg_quote($path);
        return $this->where('path', 'REGEXP', "^{$path}(/.*)?$");
    }
}
