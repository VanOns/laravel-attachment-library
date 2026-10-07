<?php

namespace VanOns\LaravelAttachmentLibrary\Test\Fixtures;

use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class CustomAttachment extends Attachment
{
    protected $table = 'attachments';
}
