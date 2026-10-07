<?php

namespace VanOns\LaravelAttachmentLibrary\View\Components\Concerns;

use VanOns\LaravelAttachmentLibrary\DataTransferObjects\Filename;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

/**
 * Resolves the component's `src` (an attachment, its id or its filename) to an attachment.
 */
trait RetrievesAttachment
{
    protected function retrieveAttachment(): ?Attachment
    {
        if ($this->src instanceof Attachment) {
            return $this->src;
        }

        if (is_numeric($this->src)) {
            return Attachment::find($this->src);
        }

        if (is_string($this->src)) {
            return Attachment::whereFilename(new Filename($this->src))->first();
        }

        return null;
    }
}
