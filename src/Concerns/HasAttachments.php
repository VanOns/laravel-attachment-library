<?php

namespace VanOns\LaravelAttachmentLibrary\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

trait HasAttachments
{
    public function attachments(): MorphToMany
    {
        return $this->morphToMany(Config::get('attachment-library.model', Attachment::class), 'attachable');
    }
}
