<?php

namespace VanOns\LaravelAttachmentLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use VanOns\LaravelAttachmentLibrary\Enums\AttachmentType;
use VanOns\LaravelAttachmentLibrary\Models\Attachment;

class AttachmentController
{
    public function __invoke(Request $request, Attachment $attachment): Response
    {
        return response(Storage::disk($attachment->disk)->get($attachment->full_path), headers: [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => AttachmentType::isRenderable($attachment->type)
                ? 'inline'
                : 'attachment; filename="' . $attachment->filename . '"',
        ]);
    }
}
