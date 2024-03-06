<?php

namespace VanOns\LaravelAttachmentLibrary;

use Illuminate\Database\Eloquent\Builder;

class AttachmentQueryBuilder extends Builder
{
    /**
     * Filter files by disk
     */
    public function whereDisk(string $disk): AttachmentQueryBuilder
    {
        return $this->where('disk', $disk);
    }

    /**
     * Filter files by path
     */
    public function wherePath(?string $path): AttachmentQueryBuilder
    {
        return $this->where('path', $path);
    }

    /**
     * Filter files like path
     */
    public function whereLikePath(?string $path): AttachmentQueryBuilder
    {
        return $this->where('path', 'LIKE', $path);
    }
}
