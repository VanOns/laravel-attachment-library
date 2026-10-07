<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
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

        $disposition = AttachmentType::isRenderable($attachment->type)
            ? HeaderUtils::DISPOSITION_INLINE
            : HeaderUtils::DISPOSITION_ATTACHMENT;

        if (! $attachment->isRemote()) {
            return response()
                ->file($attachment->absolute_path, ['Content-Type' => $attachment->mime_type])
                ->setContentDisposition($disposition, $attachment->filename);
        }

        return Storage::disk($attachment->disk)->response(
            $attachment->full_path,
            $attachment->filename,
            ['Content-Type' => $attachment->mime_type],
            $disposition,
        );
    }
}
