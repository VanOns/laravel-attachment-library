<?php

namespace VanOns\LaravelAttachmentLibrary\Concerns;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

trait HasAttachments
{
    public function attachments(): MorphToMany
    {
        return $this->morphToMany(Config::get('attachments.model', Attachment::class), 'attachable');
    }
}
