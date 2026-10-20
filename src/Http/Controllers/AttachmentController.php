<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentController
{
    /**
     * Local files are served as a file response, which supports range requests: without them, a video
     * player downloads the whole file before playing. Remote files are streamed from their disk.
     */
    public function __invoke(Request $request, Attachment $attachment): Response
    {
        abort_unless($attachment->fileExists(), 404);

        return $attachment->getFileResponse();
    }
}
