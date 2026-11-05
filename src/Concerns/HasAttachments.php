<?php

namespace VanOns\LaravelAttachmentLibrary\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

trait HasAttachments
{
    public function attachments(): MorphToMany
    {
        // `model` is the key from before `class_mapping`, kept for apps that set it.
        $class = Config::get('attachment-library.model') ?? Config::get('attachment-library.class_mapping.attachment', Attachment::class);

        // The pivot key would otherwise follow the class name of an extended model.
        return $this->morphToMany($class, 'attachable', relatedPivotKey: 'attachment_id')->orderByPivot('order');
    }

    public function attachmentCollection(string $collection): MorphToMany
    {
        return $this->attachments()->wherePivot('collection', $collection);
    }
}
