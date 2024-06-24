<?php

namespace VanOns\LaravelAttachmentLibrary\Observers;

use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentObserver
{
    public function creating(Attachment $attachment): void
    {
        $attachment->created_by = auth()->user()->getAuthIdentifier();
        $attachment->updated_by = auth()->user()->getAuthIdentifier();
    }

    public function updating(Attachment $attachment): void
    {
        $attachment->updated_by = auth()->user()->getAuthIdentifier();
    }
}
